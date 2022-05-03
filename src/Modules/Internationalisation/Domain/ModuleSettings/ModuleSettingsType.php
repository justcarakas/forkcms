<?php

namespace ForkCMS\Modules\Internationalisation\Domain\ModuleSettings;

use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Form\FieldsetType;
use ForkCMS\Core\Domain\Form\TabsType;
use ForkCMS\Modules\Installer\Domain\Database\DatabaseStepConfiguration;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\LocaleType;
use ForkCMS\Modules\Internationalisation\Domain\ModuleSettings\Command\ChangeModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\Twig\FormatSettingsType;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ModuleSettingsType extends AbstractType
{
    public function __construct(private readonly InstalledLocaleRepository $installedLocaleRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $installedLocales = $this->installedLocaleRepository->findAll();
        $localeChoices = array_map(
            static fn (InstalledLocale $locale): Locale => $locale->getLocale(),
            $installedLocales
        );
        $tabs = [];
        foreach ($localeChoices as $locale) {
            $tabs[$locale->asTranslatable()] = static function (FormBuilderInterface $builder): void {
                $builder->add(
                    'backend',
                    FieldsetType::class,
                    [
                        'label' => Application::BACKEND,
                        'fields' => static function (FormBuilderInterface $builder): void {
                            $builder->add(
                                'isEnabledForUser',
                                CheckboxType::class,
                                [
                                    'label' => 'lbl.EnabledForUser',
                                    'required' => false,
                                    'label_attr' => ['class' => 'checkbox-switch'],
                                ]
                            );
                        }
                    ]
                )->add(
                    'frontend',
                    FieldsetType::class,
                    [
                        'label' => Application::FRONTEND,
                        'fields' => static function (FormBuilderInterface $builder): void {
                            $builder->add(
                                'isEnabledForWebsite',
                                CheckboxType::class,
                                [
                                    'label' => 'lbl.EnabledForWebsite',
                                    'required' => false,
                                    'label_attr' => ['class' => 'checkbox-switch'],
                                ]
                            )->add(
                                'isEnabledForBrowserLocaleRedirect',
                                CheckboxType::class,
                                [
                                    'label' => 'lbl.EnabledForBrowserLocaleRedirect',
                                    'required' => false,
                                    'label_attr' => ['class' => 'checkbox-switch'],
                                ]
                            )->add('settings', FormatSettingsType::class, ['label' => false]);
                        }
                    ]
                );
            };
        }

        $builder->add(
            'defaultForUser',
            LocaleType::class,
            [
                'label' => 'lbl.DefaultForUser',
                'required' => true,
                'choices' => $localeChoices,
            ]
        )->add(
            'defaultForWebsite',
            LocaleType::class,
            [
                'label' => 'lbl.DefaultForWebsite',
                'required' => true,
                'choices' => $localeChoices,
            ]
        )->add(
            'installedLocales',
            TabsType::class,
            [
                'tabs' => $tabs,
                'inherit_data' => false,
                'tab_inherit_data' => false,
                'tab_attr' => ['class' => 'fieldset-tab-pane'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ChangeModuleSettings::class);
        $resolver->setDefault('constraints', [
            new Callback(
                [
                    'callback' => static function (
                        ChangeModuleSettings $changeModuleSettings,
                        ExecutionContextInterface $context,
                    ): void {
                        try {
                            $changeModuleSettings->validateDefaults();
                        } catch (RuntimeException) {
                            $context->addViolation('err.SomethingWentWrong');
                        }
                    },
                ]
            ),
        ]);
    }
}
