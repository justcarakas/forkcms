<?php

namespace Backend\Modules\Faq\Domain\Question;

use Backend\Modules\Faq\Domain\Category\Category;
use Common\Form\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

final class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'label' => 'lbl.Category',
            ]
        );
        $builder->add(
            'visibleOnPhone',
            CheckboxType::class,
            [
                'label' => 'lbl.VisibleOnPhone',
            ]
        );
        $builder->add(
            'visibleOnTablet',
            CheckboxType::class,
            [
                'label' => 'lbl.VisibleOnTablet',
            ]
        );
        $builder->add(
            'visibleOnDesktop',
            CheckboxType::class,
            [
                'label' => 'lbl.VisibleOnDesktop',
            ]
        );
        $builder->add(
            'translations',
            CollectionType::class,
            [
                'entry_type' => QuestionTranslationType::class,
            ]
        );
        $builder->add(
            'saveAsDraft',
            SubmitType::class,
            ['label' => 'lbl.SaveAsDraft']
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => QuestionDataTransferObject::class]);
    }
}
