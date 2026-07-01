<?php

declare(strict_types=1);
/**
 * @global string $class_name
 * @global string $namespace
 */
?>
<?= "<?php\n\ndeclare(strict_types=1);\n"; ?>

namespace <?= $namespace ?>;

use ForkCMS\Core\Domain\DependencyInjection\ForkModuleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class <?= $class_name ?> extends ForkModuleExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->getLoader($container)->load('services.yaml');
    }
}