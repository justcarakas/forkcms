<?php

namespace ForkCMS\Core\Installer\Domain\Module;

use ForkCMS\Core\Domain\Module\ModuleInstallerLocator;
use ForkCMS\Core\Domain\Module\ModuleName;
use ForkCMS\Modules\Blog\Installer\BlogInstaller;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form to select modules to install
 */
class ModulesType extends AbstractType implements DataTransformerInterface
{
    public function __construct(private ModuleInstallerLocator $moduleInstallerLocator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $requiredModules = $this->moduleInstallerLocator->getRequiredModuleNames();
        $builder->add(
            'modules',
            ChoiceType::class,
            [
                'choices' => $this->moduleInstallerLocator->getModuleNamesForOverview(),
                'choice_value' => static fn(ModuleName $moduleName): string => $moduleName->getName(),
                'choice_label' => static fn(ModuleName $moduleName): string => $moduleName->getName(),
                'preferred_choices' => static function (ModuleName $moduleName) use ($requiredModules): bool {
                    return in_array($moduleName, $requiredModules);
                },
                'choice_attr' => static function (ModuleName $moduleName) use ($requiredModules): array {
                    if (in_array($moduleName, $requiredModules)) {
                        return [
                            'checked' => 'checked',
                            'disabled' => 'disabled',
                        ];
                    }

                    return [];
                },
                'expanded' => true,
                'multiple' => true,
                'choice_translation_domain' => false,
            ]
        )->add(
            'installExampleData',
            CheckboxType::class,
            [
                'label' => 'Install example data',
                'required' => false,
            ]
        )->add(
            'differentDebugEmail',
            CheckboxType::class,
            [
                'label' => 'Use a specific debug email address',
                'required' => false,
                'attr' => [
                    'data-fork-cms-role' => 'different-debug-email',
                ],
            ]
        )->add(
            'debugEmail',
            EmailType::class,
            [
                'required' => false,
                'attr' => [
                    'data-fork-cms-role' => 'debug-email',
                ],
            ]
        )->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ModulesStepConfiguration::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'install_modules';
    }

    public function transform($value): ModulesStepConfiguration
    {
        return $value;
    }

    public function reverseTransform($value): ModulesStepConfiguration
    {
        if (!$value instanceof ModulesStepConfiguration) {
            throw new InvalidArgumentException('Only an instance of ModulesStepConfiguration is allowed here');
        }

        $value->normalise($this->moduleInstallerLocator);

        return $value;
    }
}
