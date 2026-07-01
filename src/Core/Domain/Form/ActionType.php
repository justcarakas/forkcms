<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form;

use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/** @extends AbstractType<array<string, mixed>> */
class ActionType extends AbstractType
{
    public function __construct(protected RouterInterface $router)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($options['actionSlug']->generateRoute($this->router, $options['get_parameters']));

        $builder->add($options['id_field_name'], HiddenType::class);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('actionSlug');
        $resolver->addAllowedTypes('actionSlug', ActionSlug::class);

        $resolver->setDefaults(
            [
                'id_field_name' => 'id',
                'get_parameters' => [], // Get parameters to be added to the generated action url
            ]
        );
    }
}
