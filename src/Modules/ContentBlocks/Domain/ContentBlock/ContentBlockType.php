<?php

declare(strict_types=1);

namespace ForkCMS\Modules\ContentBlocks\Domain\ContentBlock;

use Doctrine\ORM\QueryBuilder;
use ForkCMS\Core\Domain\Application\Application;
use ForkCMS\Core\Domain\Form\DataGridType;
use ForkCMS\Core\Domain\Form\EditorType;
use ForkCMS\Core\Domain\Form\SwitchType;
use ForkCMS\Core\Domain\Form\TabsType;
use ForkCMS\Core\Domain\Form\TitleType;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Twig\ForkTemplateLoader;
use Pageon\DoctrineDataGridBundle\Column\Column;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGridFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/** @extends AbstractType<ContentBlockDataTransferObject> */
final class ContentBlockType extends AbstractType
{
    public function __construct(
        private readonly ForkTemplateLoader $forkTemplateLoader,
        private readonly DataGridFactory $dataGridFactory,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['showRevisionsForContentBlockId'] === null) {
            $this->buildContentForm($builder, $options);

            return;
        }

        $builder->add('contentBlock', TabsType::class, [
            'tabs' => [
                'lbl.Content' => function (FormBuilderInterface $builder) use ($options): void {
                    $this->buildContentForm($builder, $options);
                },
                'lbl.Revisions' => function (FormBuilderInterface $builder) use ($options): void {
                    $builder->add(
                        'revisions',
                        DataGridType::class,
                        [
                            'data_grid' => $this->dataGridFactory->forEntity(
                                Revision::class,
                                static function (QueryBuilder $queryBuilder) use ($options): void {
                                    $queryBuilder
                                        ->where('Revision.contentBlock = :content_block_id')
                                        ->setParameter('content_block_id', $options['showRevisionsForContentBlockId'])
                                        ->andWhere('Revision.archivedOn IS NOT NULL');
                                },
                                null,
                                null,
                                ...$this->getRevisionExtraColumns()
                            ),
                        ]
                    );
                },
            ],
        ]);
    }

    /** @param array<string,mixed> $options */
    private function buildContentForm(FormBuilderInterface $builder, array $options): void
    {
        $isEnabledOptions = [
            'label' => 'lbl.VisibleOnSite',
            'required' => false,
        ];

        if (!array_key_exists('data', $options)) {
            $isEnabledOptions['attr']['checked'] = 'checked';
        }

        $templates = $this->forkTemplateLoader->getPossibleTemplates(
            ModuleName::fromFQCN(self::class),
            Application::FRONTEND,
            'Widgets'
        );

        $builder
            ->add('title', TitleType::class)
            ->add(
                'text',
                EditorType::class,
                ['required' => true, 'label' => 'lbl.Content']
            );
        if (count($templates) > 1) {
            $builder->add('template', ChoiceType::class, [
                'required' => true,
                'label' => 'lbl.Template',
                'choices' => $templates,
                'choice_translation_domain' => false,
                'preferred_choices' => [Revision::DEFAULT_TEMPLATE],
            ]);
        }
        $builder->add('isEnabled', SwitchType::class, $isEnabledOptions);
    }

    /** @return Column[] */
    private function getRevisionExtraColumns(): array
    {
        $columns = [
            Column::createPropertyColumn(
                name: 'archivedOn',
                label: 'lbl.ArchivedOn',
                entityAlias: 'Revision',
                sortable: true,
                filterable: false,
                order: 4,
            ),
        ];

        if ($this->authorizationChecker->isGranted(ModuleAction::ROLE_PREFIX . 'CONTENT_BLOCKS__CONTENT_BLOCK_EDIT')) {
            $columns[] = Column::createActionColumn(
                label: 'lbl.LoadRevision',
                order: 100,
                route: 'backend_action',
                routeAttributesCallback: [Revision::class, 'dataGridEditLinkCallback'],
                class: 'btn btn-primary btn-sm',
                iconClass: 'fa fa-edit',
                columnAttributes: ['class' => 'fork-data-grid-action'],
            );
        }

        return $columns;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', ContentBlockDataTransferObject::class);
        $resolver->setDefault('showRevisionsForContentBlockId', null);
        $resolver->setAllowedTypes('showRevisionsForContentBlockId', ['int', 'null']);
    }
}
