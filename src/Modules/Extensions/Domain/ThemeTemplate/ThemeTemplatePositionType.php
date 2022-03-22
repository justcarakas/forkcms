<?php

namespace ForkCMS\Modules\Extensions\Domain\ThemeTemplate;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use ForkCMS\Core\Domain\Form\CollectionType;
use ForkCMS\Modules\Frontend\Domain\Block\Block;
use ForkCMS\Modules\Frontend\Domain\Block\BlockRepository;
use ForkCMS\Modules\Frontend\Domain\Block\Type;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ThemeTemplatePositionType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => TranslationKey::label('Name'),
                ]
            )
            ->add(
                'block',
                CollectionType::class,
                [
                    'mapped' => false,
                    'label' => TranslationKey::label('PositionBlock'),
                    'entry_type' => EntityType::class,
                    'entry_options' => [
                        'class' => Block::class,
                        'choice_label' => fn (Block $block): string => $block->trans($this->translator),
                        'query_builder' => static function (BlockRepository $repository): QueryBuilder {
                            return $repository->createQueryBuilder('b')
                                ->andWhere('b.type != :action')
                                ->setParameter('action', Type::ACTION->value)
                                ->andWhere('b.hidden = :hidden')
                                ->setParameter('hidden', false)
                                ->addOrderBy('b.type', Criteria::ASC)
                                ->addOrderBy('b.position', Criteria::ASC);
                        },
                        'label' => false,
                        'multiple' => false,
                        'group_by' => fn (Block $block): string => $block->getType()->trans($this->translator),
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'allow_sequence' => true,
                ]
            );
    }
}
