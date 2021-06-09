<?php

namespace ForkCMS\Modules\Settings\Backend\Actions;

use ForkCMS\Core\Backend\Domain\Action\ActionIndex as BackendBaseActionIndex;
use ForkCMS\Core\Backend\Domain\Form\Form as BackendForm;
use ForkCMS\Core\Backend\Helper\Model as BackendModel;
use ForkCMS\Modules\Internationalisation\Backend\Domain\Translator\Language as BL;
use ForkCMS\Modules\Pages\Backend\Helper\Model as BackendPagesModel;
use ForkCMS\Core\Common\ModulesSettings;

/**
 * This is the SEO-action, it will display a form to set SEO settings
 */
class Seo extends BackendBaseActionIndex
{
    /**
     * The form instance
     *
     * @var BackendForm
     */
    private $form;

    /**
     * @var bool
     */
    private $isMultiLangual;

    /**
     * The array of active languages
     *
     * @var array
     */
    private $languages = [];

    /**
     * Array of pages in the current working language
     * @var array
     */
    private $workingLanguagePages = [];

    /**
     * Fields for linking the pages through the languages
     *
     * @var array
     */
    private $langFields = [];


    public function execute(): void
    {
        parent::execute();

        $this->isMultiLangual = BackendModel::getContainer()->getParameter('site.multilanguage');

        $this->loadForm();
        $this->validateForm();
        $this->parse();
        $this->display();
    }

    private function loadForm(): void
    {
        $this->form = new BackendForm('settingsSeo');
        $this->form->addCheckbox('seo_noodp', $this->get(ModulesSettings::class)->get('Core', 'seo_noodp', false));
        $this->form->addCheckbox('seo_noydir', $this->get(ModulesSettings::class)->get('Core', 'seo_noydir', false));
        $this->form->addCheckbox(
            'seo_nofollow_in_comments',
            $this->get(ModulesSettings::class)->get('Core', 'seo_nofollow_in_comments', false)
        );

        if ($this->isMultiLangual) {
            $this->workingLanguagePages = BackendPagesModel::getPagesForDropdown(
                BL::getWorkingLanguage()
            );
            $this->languages = BL::getWorkingLanguages();

            // loop through pages and build dropdown for each language
            foreach ($this->workingLanguagePages as $pageId => $pageTitle) {
                $pageData = BackendPagesModel::get($pageId);
                foreach ($this->languages as $lang => $language) {
                    if ($lang == BL::getWorkingLanguage()) {
                        $this->langFields[$pageId][$lang]['title'] = $pageTitle;
                    } else {
                        $langPages = BackendPagesModel::getPagesForDropdown($lang);
                        $ddn = $this->form->addDropdown('page_' . $lang . '_' . $pageId, $langPages, isset($pageData['data']['hreflang_' . $lang]) ? $pageData['data']['hreflang_' . $lang] : null)->setDefaultElement('');
                        $this->langFields[$pageId][$lang]['field'] = $ddn->parse();
                    }
                }
            }
        }
    }

    protected function parse(): void
    {
        parent::parse();

        if ($this->isMultiLangual) {
            $this->template->assign('langFields', $this->langFields);
            $this->template->assign('languages', $this->languages);
        }

        $this->form->parse($this->template);
    }

    private function validateForm(): void
    {
        // is the form submitted?
        if ($this->form->isSubmitted()) {
            // no errors ?
            if ($this->form->isCorrect()) {
                // smtp settings
                $this->get(ModulesSettings::class)->set('Core', 'seo_noodp', $this->form->getField('seo_noodp')->getValue());
                $this->get(ModulesSettings::class)->set('Core', 'seo_noydir', $this->form->getField('seo_noydir')->getValue());
                $this->get(ModulesSettings::class)->set(
                    'Core',
                    'seo_nofollow_in_comments',
                    $this->form->getField('seo_nofollow_in_comments')->getValue()
                );

                if ($this->isMultiLangual) {
                    // save the linked pages for each language
                    foreach ($this->workingLanguagePages as $pageId => $pageTitle) {
                        $pageData = BackendPagesModel::get($pageId);
                        $data['data'] = $pageData['data'];

                        foreach ($this->languages as $lang => $language) {
                            if ($lang != BL::getWorkingLanguage()) {
                                $data['data']['hreflang_' . $lang] = $this->form->getField('page_' . $lang . '_' . $pageId)->getValue();
                            }
                        }

                        BackendPagesModel::updateRevisionData($pageData['id'], $pageData['revision_id'], $data);
                    }
                }

                // assign report
                $this->template->assign('report', true);
                $this->template->assign('reportMessage', BL::msg('Saved'));
            }
        }
    }
}
