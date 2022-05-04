<?php

namespace ForkCMS\Modules\Pages\Installer;

use Doctrine\ORM\Query\ResultSetMapping;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Extensions\Domain\ThemeTemplate\ThemeTemplate;
use ForkCMS\Modules\Frontend\Domain\Meta\Meta;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\Pages\Backend\Actions\PageIndex;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Revision\Command\CreateRevision;
use ForkCMS\Modules\Pages\Domain\Revision\MenuType;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use ForkCMS\Modules\Pages\Domain\RevisionBlock\RevisionBlock;

final class PagesInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;

    public function preInstall(): void
    {
        $this->createTableForEntities(Page::class, Revision::class, RevisionBlock::class);
    }

    public function install(): void
    {
        $this->getOrCreateBackendNavigationItem(
            label: TranslationKey::label('Pages'),
            slug: PageIndex::getActionSlug(),
            sequence: 1,
        );
    }

    /**
     * @param Locale[] $locales
     * @param callable(Locale, CreateRevision)|null $createRevision
     */
    public function createPage(
        array $locales,
        string $title,
        MenuType $type,
        ?Page $parentPage = null,
        ?Page $page = null,
        ?callable $callback = null
    ): Page {
        foreach ($locales as $locale) {
            if ($page === null) {
                $page = new Page($locale);
            }
            $revision = new CreateRevision($page, $locale, false);
            $revision->title = $title;
            $revision->parentPage = $parentPage;
            $revision->meta = Meta::forName($title);
            $revision->themeTemplate = $this->entityManager->getReference(ThemeTemplate::class, 1);
            $revision->type = $type;
            $revision->settings['navigation_title'] = $title;

            if ($callback !== null) {
                $callback($locale, $revision);
            }

            $this->dispatchCommand($revision);
        }

        return $page;
    }

    private function setPagesAutoIncrement(int $startValue): void
    {
        $this->entityManager->createNativeQuery('ALTER TABLE pages__page AUTO_INCREMENT=' . $startValue, new ResultSetMapping())->execute();
    }
}
