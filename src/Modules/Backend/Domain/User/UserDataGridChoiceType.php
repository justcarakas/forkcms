<?php

declare(strict_types=1);

namespace ForkCMS\Modules\Backend\Domain\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<array<string, mixed>> */
final class UserDataGridChoiceType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'class' => User::class,
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choice_label' => static fn (User $user): string => $user->getUserIdentifier(),
            ]
        );
    }

    #[\Override]
    public function getParent(): string
    {
        return EntityType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'user_data_grid_choice';
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $possibleUsers = $options['choice_loader']->loadChoiceList()->getChoices();

        $view->vars['possibleUsers'] = array_combine(
            array_map(static fn (User $user): int => $user->id, $possibleUsers),
            $possibleUsers
        );
    }
}
