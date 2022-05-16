<?php

namespace ForkCMS\Modules\Frontend\Domain\ModuleSettings;

use ForkCMS\Core\Domain\Form\CollectionType;
use ForkCMS\Core\Domain\Form\FieldsetType;
use ForkCMS\Core\Domain\Form\TabsType;
use ForkCMS\Modules\Extensions\Domain\Module\Command\ChangeModuleSettings;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

final class ModuleSettingsType extends AbstractType
{
    public function __construct(
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly ModuleSettings $moduleSettings,
    ) {
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
            $tabs[$locale->asTranslatable()] = static function (FormBuilderInterface $builder) use ($locale): void {
                $builder->add(
                    'site_title_' . $locale->value,
                    TextType::class,
                    [
                        'label' => 'lbl.SiteTitle',
                    ]
                );
            };
        }

        $builder->add(
            'locale_specific_settings',
            TabsType::class,
            [
                'tabs' => $tabs,
                'tab_attr' => ['class' => 'fieldset-tab-pane'],
            ]
        )->add(
            'scripts',
            FieldsetType::class,
            [
                'label' => 'lbl.Scripts',
                'fields' => static function (FormBuilderInterface $builder): void {
                    $builder->add(
                        'site_html_head',
                        TextareaType::class,
                        [
                            'label' => 'lbl.SiteHtmlHead',
                            'label_html' => true,
                            'help' => 'msg.HelpSiteHtmlHead',
                            'help_html' => true,
                            'required' => false,
                        ]
                    )->add(
                        'site_html_start_of_body',
                        TextareaType::class,
                        [
                            'label' => 'lbl.SiteHtmlStartOfBody',
                            'label_html' => true,
                            'help' => 'msg.HelpSiteHtmlStartOfBody',
                            'help_html' => true,
                            'required' => false,
                        ]
                    )->add(
                        'site_html_end_of_body',
                        TextareaType::class,
                        [
                            'label' => 'lbl.SiteHtmlEndOfBody',
                            'label_html' => true,
                            'help' => 'msg.HelpSiteHtmlEndOfBody',
                            'required' => false,
                        ]
                    );
                }
            ]
        )->add(
            'privacy_consents',
            FieldsetType::class,
            [
                'label' => 'lbl.PrivacyConsents',
                'fields' => function (FormBuilderInterface $builder): void {
                    $submittedShow = $_POST['module_settings']['privacy_consents']['show_consent_dialog'] ?? null;
                    $showConsentDialog = $this->moduleSettings->get(
                        ModuleName::fromString('Frontend'),
                        'show_consent_dialog'
                    );
                    $showConsentDialog = (bool) ($submittedShow ?? $showConsentDialog);

                    $builder->add(
                        'show_consent_dialog',
                        CheckboxType::class,
                        [
                            'label' => 'lbl.ShowConsentDialog',
                            'help' => 'msg.HelpShowConsentDialog',
                            'required' => false,
                            'label_attr' => ['class' => 'checkbox-switch'],
                            'attr' => [
                                'data-bs-toggle' => 'collapse',
                                'data-bs-target' => '#module_settings_privacy_consents_privacy_consent_levels'
                            ],
                        ]
                    )->add(
                        'privacy_consent_levels',
                        CollectionType::class,
                        [
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Regex(pattern: '/^[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*$/i', message: 'err.InvalidVariableName', )
                                ],
                                'help_html' => true,
                            ],
                            'allow_add' => true,
                            'allow_delete' => true,
                            'allow_sequence' => true,
                            'by_reference' => false,
                            'label' => 'lbl.TechnicalName',
                            'help' => 'msg.HelpPrivacyConsentLevels',
                            'required' => false,
                            'attr' => [
                                'class' => 'collapse' . ($showConsentDialog ? ' show' : '')
                            ],
                        ]
                    );
                }
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', ChangeModuleSettings::class);
    }
}
