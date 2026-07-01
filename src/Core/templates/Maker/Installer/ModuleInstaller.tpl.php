<?php

declare(strict_types=1);

use ForkCMS\Core\Domain\Maker\Util\Entity;

/**
 * @global string $namespace
 * @global string $class_name
 * @global bool $isRequired
 * @global bool $hideFromOverview
 * @global Entity[] $entities
 */
?>
<?= "<?php\n\ndeclare(strict_types=1);\n"; ?>

namespace <?= $namespace ?>;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
<?php foreach ($entities as $entity) { ?>
use <?= $entity->entityClassNameDetails->getFullName() ?>;
<?php }; ?>
final class <?= $class_name; ?> extends ModuleInstaller
{
<?php if ($isRequired): ?>
    public const bool IS_REQUIRED = true;
<?php endif; ?>
<?php if ($hideFromOverview): ?>
    public const bool IS_VISIBLE_IN_OVERVIEW = false;
<?php endif; ?>

    public function preInstall(): void
    {
        $this->createTableForEntities(
<?php foreach ($entities as $entity) { ?>
            <?= $entity->getName() ?>::class,
<?php }; ?>
        );
    }

    #[\Override]
    public function install(): void
    {
        throw new \RuntimeException('Not implemented yet');
    }
}
