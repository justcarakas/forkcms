<?php

namespace ForkCMS\Modules\Blog\Domain\Category;

use ForkCMS\Core\Backend\Domain\Form\DeleteType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class BlogDeleteType extends DeleteType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('categoryId', HiddenType::class);
    }
}
