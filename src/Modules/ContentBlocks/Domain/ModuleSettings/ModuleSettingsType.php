<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ModuleSettings;

use ForkCMS\Core\Domain\Form\FieldsetType;
use ForkCMS\Modules\ContentBlocks\Domain\ContentBlock\Revision;
use ForkCMS\Modules\Extensions\Domain\Module\Command\ChangeModuleSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<array<string, mixed>> */
final class ModuleSettingsType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'revisions',
            FieldsetType::class,
            [
                'label' => 'lbl.Revisions',
                'fields' => static function (FormBuilderInterface $builder): void {
                    $builder->add(
                        Revision::SETTING_MAX_REVISIONS_NAME,
                        ChoiceType::class,
                        [
                            'label' => 'lbl.MaxRevisions',
                            'help' => 'msg.HelpMaxRevisions',
                            'choices' => array_combine(range(1, 30), range(1, 30)),
                        ]
                    );
                },
            ]
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', ChangeModuleSettings::class);
    }
}
