<?php

namespace ForkCMS\Core\Backend\Helper;

use ForkCMS\Modules\Internationalisation\Backend\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BackendLanguage;
use ForkCMS\Modules\Extensions\Backend\Helper\Model as BackendExtensionsModel;
use ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtra;
use ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtraNotFountException;
use ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtraRepository;
use ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtraType;
use ForkCMS\Modules\Pages\Domain\PageBlock\PageBlockRepository;
use ForkCMS\Modules\Pages\Backend\Helper\Model as BackendPagesModel;
use ForkCMS\Modules\Internationalisation\Frontend\Domain\Translator\Language as FrontendLanguage;

/**
 * In this file we store all generic functions that we will be using in the backend.
 */
class Model extends \ForkCMS\Core\Common\Model
{
    /**
     * Checks the settings and optionally returns an array with warnings
     *
     * @return array
     */
    public static function checkSettings(): array
    {
        $warnings = [];

        // check if debug-mode is active
        if (BackendModel::getContainer()->getParameter('kernel.debug')) {
            $warnings[] = ['message' => BackendLanguage::err('DebugModeIsActive')];
        }

        // check for extensions warnings
        $warnings = array_merge($warnings, BackendExtensionsModel::checkSettings());

        return $warnings;
    }

    /**
     * Creates an URL for a given action and module
     * If you don't specify an action the current action will be used.
     * If you don't specify a module the current module will be used.
     * If you don't specify a language the current language will be used.
     *
     * @param string $action The action to build the URL for.
     * @param string $module The module to build the URL for.
     * @param string $language The language to use, if not provided we will use the working language.
     * @param array $parameters GET-parameters to use.
     * @param bool $encodeSquareBrackets Should the square brackets be allowed so we can use them in de datagrid?
     *
     * @throws \Exception If $action, $module or both are not set
     *
     * @return string
     */
    public static function createUrlForAction(
        string $action = null,
        string $module = null,
        string $language = null,
        array $parameters = null,
        bool $encodeSquareBrackets = true
    ): string {
        $language = $language ?? BackendLanguage::getWorkingLanguage();

        // checking if we have an url, because in a cronjob we don't have one
        if (self::getContainer()->has('url')) {
            // grab the URL from the reference
            $url = self::getContainer()->get('url');
            $action = $action ?? $url->getAction();
            $module = $module ?? $url->getModule();
        }

        // error checking
        if ($action === null || $module === null) {
            throw new \Exception('Action and Module must not be empty when creating an url.');
        }

        $parameters['token'] = self::getToken();
        if (self::requestIsAvailable()) {
            $queryParameterBag = self::getRequest()->query;

            // add offset, order & sort (only if not yet manually added)
            if (!isset($parameters['offset']) && $queryParameterBag->has('offset')) {
                $parameters['offset'] = $queryParameterBag->getInt('offset');
            }
            if (!isset($parameters['order']) && $queryParameterBag->has('order')) {
                $parameters['order'] = $queryParameterBag->get('order');
            }
            if (!isset($parameters['sort']) && $queryParameterBag->has('sort')) {
                $parameters['sort'] = $queryParameterBag->get('sort');
            }
        }

        $queryString = '?' . http_build_query($parameters);

        if (!$encodeSquareBrackets) {
            // we use things like [id] to parse database column data in so we need to unescape those
            $queryString = str_replace([urlencode('['), urlencode(']')], ['[', ']'], $queryString);
        }

        return self::get('router')->generate(
            'backend',
            [
                '_locale' => $language,
                'module' => self::camelCaseToLowerSnakeCase($module),
                'action' => self::camelCaseToLowerSnakeCase($action),
            ]
        ) . $queryString;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function camelCaseToLowerSnakeCase(string $string): string
    {
        return mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Delete a page extra by module, type or data.
     *
     * Data is a key/value array. Example: array(id => 23, language => nl);
     *
     * @param string $module The module wherefore the extra exists.
     * @param string $moduleExtraType The type of extra, possible values are block, homepage, widget.
     * @param array $data Extra data that exists.
     */
    public static function deleteExtra(
        string $module = null,
        ModuleExtraType $moduleExtraType = null,
        array $data = null
    ): void {
        $moduleExtraRepository = self::get(ModuleExtraRepository::class);

        $parameters = [];

        if ($module !== null) {
            $parameters['module'] = $module;
        }

        if ($moduleExtraType !== null) {
            $parameters['type'] = $moduleExtraType;
        }

        // get extras
        $extras = $moduleExtraRepository->findBy($parameters);

        /** @var ModuleExtra $extra */
        foreach ($extras as $extra) {
            $extraData = $extra->getData();
            // if we have $data parameter set and $extraData not null we should not delete such extra
            if ($data !== null && $extraData === null) {
                continue;
            }

            if ($data !== null && $extraData !== null && is_array($extraData)) {
                foreach ($data as $dataKey => $dataValue) {
                    if (isset($extraData[$dataKey]) && $dataValue !== $extraData[$dataKey]) {
                        continue 2;
                    }
                }
            }

            $moduleExtraRepository->delete($extra);
        }
    }

    /**
     * Delete a page extra by its id
     *
     * @param int $id The id of the extra to delete.
     * @param bool $deleteBlock Should the block be deleted? Default is false.
     *
     * @deprecated Use \ForkCMS\Modules\Pages\Domain\ModuleExtra\ModuleExtraRepository instead
     */
    public static function deleteExtraById(int $id, bool $deleteBlock = false): void
    {
        /** @var ModuleExtraRepository $moduleExtraRepository */
        $moduleExtraRepository = self::get(ModuleExtraRepository::class);

        $moduleExtra = $moduleExtraRepository->find($id);

        if (!$moduleExtra instanceof ModuleExtra) {
            throw new ModuleExtraNotFountException();
        }

        $moduleExtraRepository->delete($moduleExtra);

        if ($deleteBlock) {
            /** @var PageBlockRepository $pageBlockRepository */
            $pageBlockRepository = BackendModel::get(PageBlockRepository::class);
            $pageBlockRepository->deleteByExtraId($id);

            return;
        }

        /** @var PageBlockRepository $pageBlockRepository */
        $pageBlockRepository = BackendModel::get(PageBlockRepository::class);
        $pageBlockRepository->clearExtraId($id);
    }

    /**
     * Delete all extras for a certain value in the data array of that module_extra.
     *
     * @param string $module The module for the extra.
     * @param string $field The field of the data you want to check the value for.
     * @param string $value The value to check the field for.
     * @param string $action In case you want to search for a certain action.
     *
     * @deprecated Not in use
     */
    public static function deleteExtrasForData(
        string $module,
        string $field,
        string $value,
        string $action = null
    ): void {
        $ids = self::getExtrasForData($module, $field, $value, $action);

        // we have extras
        if (!empty($ids)) {
            // delete extras
            self::getContainer()->get('database')->delete('PagesModuleExtra', 'id IN (' . implode(',', $ids) . ')');
        }
    }

    /**
     * Fetch the list of long date formats including examples of these formats.
     *
     * @return array
     */
    public static function getDateFormatsLong(): array
    {
        $possibleFormats = [];

        // loop available formats
        foreach ((array) self::get(ModuleSettingRepository::class)->get('Core', 'date_formats_long') as $format) {
            // get date based on given format
            $possibleFormats[$format] = \SpoonDate::getDate(
                $format,
                null,
                Authentication::getUser()->getSetting('interface_language')
            );
        }

        return $possibleFormats;
    }

    /**
     * Fetch the list of short date formats including examples of these formats.
     *
     * @return array
     */
    public static function getDateFormatsShort(): array
    {
        $possibleFormats = [];

        // loop available formats
        foreach ((array) self::get(ModuleSettingRepository::class)->get('Core', 'date_formats_short') as $format) {
            // get date based on given format
            $possibleFormats[$format] = \SpoonDate::getDate(
                $format,
                null,
                Authentication::getUser()->getSetting('interface_language')
            );
        }

        return $possibleFormats;
    }

    /**
     * Get extras for data
     *
     * @param string $module The module for the extra.
     * @param string $key The key of the data you want to check the value for.
     * @param string $value The value to check the key for.
     * @param string $action In case you want to search for a certain action.
     *
     * @return array The ids for the extras.
     *
     * @deprecated Not in use
     */
    public static function getExtrasForData(string $module, string $key, string $value, string $action = null): array
    {
        $query = 'SELECT i.id, i.data
                 FROM PagesModuleExtra AS i
                 WHERE i.module = ? AND i.data != ?';
        $parameters = [$module, 'NULL'];

        // Filter on the action if it is given.
        if ($action !== null) {
            $query .= ' AND i.action = ?';
            $parameters[] = $action;
        }

        $moduleExtras = (array) self::getContainer()->get('database')->getPairs($query, $parameters);

        // No module extra's found
        if (empty($moduleExtras)) {
            return [];
        }

        return array_keys(
            array_filter(
                $moduleExtras,
                function (?string $serializedData) use ($key, $value) {
                    $data = $serializedData === null ? [] : unserialize($serializedData, ['allowed_classes' => false]);

                    return isset($data[$key]) && (string) $data[$key] === $value;
                }
            )
        );
    }

    /**
     * Get the page-keys
     *
     * @param string $language The language to use, if not provided we will use the working language.
     *
     * @return array
     */
    public static function getKeys(string $language = null): array
    {
        if ($language === null) {
            $language = BackendLanguage::getWorkingLanguage();
        }

        return BackendPagesModel::getCacheBuilder()->getKeys(Locale::from($language));
    }

    /**
     * Get the modules that are available on the filesystem
     *
     * @param bool $includeCore Should core be included as a module?
     *
     * @return array
     */
    public static function getModulesOnFilesystem(bool $includeCore = true): array
    {
        $modules = $includeCore ? ['Core'] : [];
        $finder = new Finder();
        $directories = $finder->directories()->in(__DIR__ . '/../../Modules')->depth('==0');
        foreach ($directories as $directory) {
            $modules[] = $directory->getBasename();
        }

        return $modules;
    }

    /**
     * Fetch the list of modules, but for a dropdown.
     *
     * @return array
     */
    public static function getModulesForDropDown(): array
    {
        $dropDown = ['Core' => 'Core'];

        // fetch modules
        $modules = self::getModules();

        // loop and add into the return-array (with correct label)
        foreach ($modules as $module) {
            $dropDown[$module] = \SpoonFilter::ucfirst(BackendLanguage::lbl(\SpoonFilter::toCamelCase($module)));
        }

        return $dropDown;
    }

    /**
     * Get the navigation-items
     *
     * @param string $language The language to use, if not provided we will use the working language.
     *
     * @return array
     */
    public static function getNavigation(string $language = null): array
    {
        if ($language === null) {
            $language = BackendLanguage::getWorkingLanguage();
        }

        $cacheBuilder = BackendPagesModel::getCacheBuilder();

        return $cacheBuilder->getNavigation(Locale::from($language));
    }

    /**
     * Fetch the list of number formats including examples of these formats.
     *
     * @return array
     */
    public static function getNumberFormats(): array
    {
        return (array) self::get(ModuleSettingRepository::class)->get('Core', 'number_formats');
    }

    /**
     * Fetch the list of time formats including examples of these formats.
     *
     * @return array
     */
    public static function getTimeFormats(): array
    {
        $possibleFormats = [];
        $interfaceLanguage = Authentication::getUser()->getSetting('interface_language');

        foreach (self::get(ModuleSettingRepository::class)->get('Core', 'time_formats') as $format) {
            $possibleFormats[$format] = \SpoonDate::getDate($format, null, $interfaceLanguage);
        }

        return $possibleFormats;
    }

    /**
     * Get the token which will protect us
     *
     * @return string
     */
    public static function getToken(): string
    {
        if (self::getSession()->has('csrf_token') && self::getSession()->get('csrf_token') !== '') {
            return self::getSession()->get('csrf_token');
        }

        $token = bin2hex(random_bytes(10));
        self::getSession()->set('csrf_token', $token);

        return $token;
    }

    /**
     * Get URL for a given pageId
     *
     * @param int $pageId The id of the page to get the URL for.
     * @param string $language The language to use, if not provided we will use the working language.
     *
     * @return string
     */
    public static function getUrl(int $pageId, string $language = null): string
    {
        if ($language === null) {
            $language = BackendLanguage::getWorkingLanguage();
        }

        // Prepend the language if the site is multi language
        $url = self::getContainer()->getParameter('site.multilanguage') ? '/' . $language . '/' : '/';

        // get the menuItems
        $keys = self::getKeys($language);

        // get the URL, if it doesn't exist return 404
        if (!isset($keys[$pageId])) {
            return self::getUrl(Page::ERROR_PAGE_ID, $language);
        }

        // return the unique URL!
        return urldecode($url . $keys[$pageId]);
    }

    /**
     * Get the URL for a give module & action combination
     *
     * @param string $module The module wherefore the URL should be build.
     * @param string $action The specific action wherefore the URL should be build.
     * @param string $language The language wherein the URL should be retrieved,
     *                         if not provided we will load the language that was provided in the URL.
     * @param array $data An array with keys and values that partially or fully match the data of the block.
     *                         If it matches multiple versions of that block it will just return the first match.
     *
     * @return string
     */
    public static function getUrlForBlock(
        string $module,
        string $action = null,
        string $language = null,
        array $data = null
    ): string {
        if ($language === null) {
            $language = BackendLanguage::getWorkingLanguage();
        }

        $pageIdForUrl = null;
        $navigation = self::getNavigation(Locale::from($language));

        $dataMatch = false;
        // loop types
        foreach ($navigation as $level) {
            // loop level
            foreach ($level as $pages) {
                // loop pages
                foreach ($pages as $pageId => $properties) {
                    // only process pages with extra_blocks that are visible
                    if (!isset($properties['extra_blocks']) || $properties['hidden']) {
                        continue;
                    }

                    // loop extras
                    /** @var ModuleExtra $extra */
                    foreach ($properties['extra_blocks'] as $extra) {
                        // direct link?
                        if ($extra->getModule() === $module
                            && $extra->getAction() === $action
                            && $extra->getAction() !== null) {
                            // if there is data check if all the requested data matches the extra data
                            if ($data !== null && $extra->getData()
                                && array_intersect_assoc($data, (array) $extra->getData()) !== $data
                            ) {
                                // It is the correct action but has the wrong data
                                continue;
                            }

                            // exact page was found, so return
                            return self::getUrl($properties['page_id'], $language);
                        }

                        if ($extra->getModule() === $module && $extra->getAction() === null) {
                            // if there is data check if all the requested data matches the extra data
                            if ($data !== null && $extra->getData() !== null) {
                                if (array_intersect_assoc($data, (array) $extra->getData()) !== $data) {
                                    // It is the correct module but has the wrong data
                                    continue;
                                }

                                $pageIdForUrl = (int) $pageId;
                                $dataMatch = true;
                            }

                            if ($data === null && $extra->getData() === null) {
                                $pageIdForUrl = (int) $pageId;
                                $dataMatch = true;
                            }

                            if (!$dataMatch) {
                                $pageIdForUrl = (int) $pageId;
                            }
                        }
                    }
                }
            }
        }

        // Page not found so return the 404 url
        if ($pageIdForUrl === null) {
            return self::getUrl(Page::ERROR_PAGE_ID, $language);
        }

        $url = self::getUrl($pageIdForUrl, $language);

        // set locale with force
        FrontendLanguage::setLocale($language, true);

        // append action
        if ($action !== null) {
            $url .= '/' . urldecode(FrontendLanguage::act(\SpoonFilter::toCamelCase($action)));
        }

        // return the unique URL!
        return $url;
    }

    /**
     * Image Delete
     *
     * @param string $module Module name.
     * @param string $filename Filename.
     * @param string $subDirectory Subdirectory.
     * @param array $fileSizes Possible file sizes.
     */
    public static function imageDelete(
        string $module,
        string $filename,
        string $subDirectory = '',
        array $fileSizes = null
    ): void {
        if (empty($fileSizes)) {
            $model = get_class_vars('Backend' . \SpoonFilter::toCamelCase($module) . 'Model');
            $fileSizes = $model['fileSizes'];
        }

        // also include the source directory
        $fileSizes[] = 'source';

        $baseDirectory = FRONTEND_FILES_PATH . '/' . $module . (empty($subDirectory) ? '/' : '/' . $subDirectory . '/');
        $filesystem = new Filesystem();
        array_walk(
            $fileSizes,
            function (string $sizeDirectory) use ($baseDirectory, $filename, $filesystem) {
                $fullPath = $baseDirectory . basename($sizeDirectory) . '/' . $filename;
                if (is_file($fullPath)) {
                    $filesystem->remove($fullPath);
                }
            }
        );
    }

    /**
     * Insert extra
     *
     * @param ModuleExtraType $type What type do you want to insert, 'block' or 'widget'.
     * @param string $module The module you are inserting this extra for.
     * @param string $action The action this extra will use.
     * @param string $label Label which will be used when you want to connect this block.
     * @param array $data Containing extra variables.
     * @param bool $hidden Should this extra be visible in frontend or not?
     * @param int $sequence
     *
     * @throws Exception If extra type is not allowed
     *
     * @return int The new extra id
     */
    public static function insertExtra(
        ModuleExtraType $type,
        string $module,
        string $action = null,
        string $label = null,
        array $data = null,
        bool $hidden = false,
        int $sequence = null
    ): int {
        /** @var ModuleExtraRepository $moduleExtraRepository */
        $moduleExtraRepository = self::get(ModuleExtraRepository::class);

        if ($sequence === null) {
            $sequence = $moduleExtraRepository->getNextSequenceByModule($module);
        }

        $moduleExtra = new ModuleExtra($module, new ModuleExtraType($type), $label ?? $module, $action ?? null, $data, $hidden, $sequence);
        $moduleExtraRepository->add($moduleExtra);
        $moduleExtraRepository->save($moduleExtra);

        return $moduleExtra->getId();
    }

    /**
     * This returns the identifier for the editor the logged in user prefers to use in forms.
     *
     * @return string
     */
    public static function getPreferredEditor(): string
    {
        $defaultPreferredEditor = self::getContainer()->getParameter('fork.form.default_preferred_editor');

        if (!Authentication::isLoggedIn()) {
            return $defaultPreferredEditor;
        }

        return Authentication::getUser()->getSetting('preferred_editor', $defaultPreferredEditor);
    }

    /**
     * Is module installed?
     *
     * @param string $module
     *
     * @return bool
     */
    public static function isModuleInstalled(string $module): bool
    {
        return in_array($module, self::getModules(), true);
    }

    /**
     * Submit ham, this call is intended for the marking of false positives, things that were incorrectly marked as
     * spam.
     *
     * @param string $userIp IP address of the comment submitter.
     * @param string $userAgent User agent information.
     * @param string $content The content that was submitted.
     * @param string $author Submitted name with the comment.
     * @param string $email Submitted email address.
     * @param string $url Commenter URL.
     * @param string $permalink The permanent location of the entry the comment was submitted to.
     * @param string $type May be blank, comment, trackback, pingback, or a made up value like "registration".
     * @param string $referrer The content of the HTTP_REFERER header should be sent here.
     * @param array $others Other data (the variables from $_SERVER).
     *
     * @throws Exception
     *
     * @return bool If everything went fine, true will be returned, otherwise an exception will be triggered.
     */
    public static function submitHam(
        string $userIp,
        string $userAgent,
        string $content,
        string $author = null,
        string $email = null,
        string $url = null,
        string $permalink = null,
        string $type = null,
        string $referrer = null,
        array $others = null
    ): bool {
        try {
            $akismet = self::getAkismet();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return false;
        }

        // try it to decide it the item is spam
        try {
            // check with Akismet if the item is spam
            return $akismet->submitHam(
                $userIp,
                $userAgent,
                $content,
                $author,
                $email,
                $url,
                $permalink,
                $type,
                $referrer,
                $others
            );
        } catch (Exception $e) {
            if (BackendModel::getContainer()->getParameter('kernel.debug')) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Submit spam, his call is for submitting comments that weren't marked as spam but should have been.
     *
     * @param string $userIp IP address of the comment submitter.
     * @param string $userAgent User agent information.
     * @param string $content The content that was submitted.
     * @param string $author Submitted name with the comment.
     * @param string $email Submitted email address.
     * @param string $url Commenter URL.
     * @param string $permalink The permanent location of the entry the comment was submitted to.
     * @param string $type May be blank, comment, trackback, pingback, or a made up value like "registration".
     * @param string $referrer The content of the HTTP_REFERER header should be sent here.
     * @param array $others Other data (the variables from $_SERVER).
     *
     * @throws Exception
     *
     * @return bool If everything went fine true will be returned, otherwise an exception will be triggered.
     */
    public static function submitSpam(
        string $userIp,
        string $userAgent,
        string $content,
        string $author = null,
        string $email = null,
        string $url = null,
        string $permalink = null,
        string $type = null,
        string $referrer = null,
        array $others = null
    ): bool {
        try {
            $akismet = self::getAkismet();
        } catch (InvalidArgumentException $invalidArgumentException) {
            return false;
        }

        // try it to decide it the item is spam
        try {
            // check with Akismet if the item is spam
            return $akismet->submitSpam(
                $userIp,
                $userAgent,
                $content,
                $author,
                $email,
                $url,
                $permalink,
                $type,
                $referrer,
                $others
            );
        } catch (Exception $e) {
            if (BackendModel::getContainer()->getParameter('kernel.debug')) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Update extra
     *
     * @param int $id The id for the extra.
     * @param string $key The key you want to update.
     * @param mixed $value The new value.
     *
     * @throws Exception If key parameter is not allowed
     */
    public static function updateExtra(int $id, string $key, $value): void
    {
        // define allowed keys
        $allowedKeys = ['label', 'action', 'data', 'hidden', 'sequence'];

        // key is not allowed
        if (!in_array($key, $allowedKeys, true)) {
            throw new Exception('The key ' . $key . ' can\'t be updated.');
        }

        /** @var ModuleExtraRepository $moduleExtraRepository */
        $moduleExtraRepository = self::get(ModuleExtraRepository::class);
        $moduleExtra = $moduleExtraRepository->find($id);

        if (!$moduleExtra instanceof ModuleExtra) {
            throw new ModuleExtraNotFountException();
        }

        $module = $moduleExtra->getModule();
        $label = $moduleExtra->getLabel();
        $type = $moduleExtra->getType();
        $action = $moduleExtra->getAction();
        $data = $moduleExtra->getData();
        $hidden = $moduleExtra->isHidden();
        $sequence = $moduleExtra->getSequence();

        // Set the value dynamically
        $$key = $value;

        $moduleExtra->update($module, $type, $label, $action, $data, $hidden, $sequence);

        $moduleExtraRepository->save($moduleExtra);
    }
}
