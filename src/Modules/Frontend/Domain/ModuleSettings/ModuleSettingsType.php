<?php

namespace ForkCMS\Modules\Frontend\Domain\ModuleSettings;

use ForkCMS\Core\Domain\Form\CollectionType;
use ForkCMS\Core\Domain\Form\FieldsetType;
use ForkCMS\Core\Domain\Form\TabsType;
use ForkCMS\Modules\Extensions\Domain\Module\Command\ChangeModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ModuleSettingsType extends AbstractType
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
            'privacy_policy',
            FieldsetType::class,
            [
                'label' => 'lbl.PrivacyPolicy',
                'fields' => static function (FormBuilderInterface $builder): void {
                    $builder->add(
                        'show_consent_dialog',
                        CheckboxType::class,
                        [
                            'label' => 'lbl.ShowConsentDialog',
                            'required' => false,
                            'label_attr' => ['class' => 'checkbox-switch'],
                        ]
                    )->add('privacy_consent_levels',
                        CollectionType::class,
                        [
                            'entry_type' => TextType::class,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'allow_sequence' => true,
                            'by_reference' => false,
                            'label' => false,
                            'required' => false,
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
