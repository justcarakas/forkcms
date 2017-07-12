<?php

namespace Backend\Modules\Blog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Modules\Blog\Form\BlogDeleteType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Language\Language as BL;
use Backend\Core\Engine\DataGridDatabase as BackendDataGridDatabase;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Blog\Engine\Model as BackendBlogModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Users\Engine\Model as BackendUsersModel;

/**
 * This is the edit-action, it will display a form to edit an existing item
 */
class Edit extends BackendBaseActionEdit
{
    /**
     * The id of the category where is filtered on
     *
     * @var int
     */
    private $categoryId;

    /**
     * DataGrid for the drafts
     *
     * @var BackendDataGridDatabase
     */
    private $dgDrafts;

    /**
     * Is the image field allowed?
     *
     * @var bool
     */
    protected $imageIsAllowed = true;

    public function execute(): void
    {
        // get parameters
        $this->id = $this->getRequest()->query->getInt('id');

        // does the item exists
        if ($this->id !== 0 && BackendBlogModel::exists($this->id)) {
            parent::execute();

            // set category id
            $this->categoryId = $this->getRequest()->query->getInt('category');
            if ($this->categoryId === 0) {
                $this->categoryId = null;
            }

            $this->getData();
            $this->loadDraftDataGrid();
            $this->loadRevisionDataGrid();
            $this->loadForm();
            $this->validateForm();
            $this->loadDeleteForm();
            $this->parse();
            $this->display();
        } else {
            // no item found, throw an exception, because somebody is fucking with our URL
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=non-existing');
        }
    }

    /**
     * Get the data
     * If a revision-id was specified in the URL we load the revision and not the actual data.
     */
    private function getData(): void
    {
        $this->record = (array) BackendBlogModel::get($this->id);
        $this->imageIsAllowed = $this->get('fork.settings')->get($this->url->getModule(), 'show_image_form', true);

        // is there a revision specified?
        $revisionToLoad = $this->getRequest()->query->getInt('revision');

        // if this is a valid revision
        if ($revisionToLoad !== 0) {
            // overwrite the current record
            $this->record = (array) BackendBlogModel::getRevision($this->id, $revisionToLoad);

            // show warning
            $this->tpl->assign('usingRevision', true);
        }

        // is there a revision specified?
        $draftToLoad = $this->getRequest()->query->getInt('draft');

        // if this is a valid revision
        if ($draftToLoad !== 0) {
            // overwrite the current record
            $this->record = (array) BackendBlogModel::getRevision($this->id, $draftToLoad);

            // show warning
            $this->tpl->assign('usingDraft', true);

            // assign draft
            $this->tpl->assign('draftId', $draftToLoad);
        }

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if (empty($this->record)) {
            $this->redirect(BackendModel::createUrlForAction('Index') . '&error=non-existing');
        }
    }

    private function loadDraftDataGrid(): void
    {
        // create datagrid
        $this->dgDrafts = new BackendDataGridDatabase(
            BackendBlogModel::QRY_DATAGRID_BROWSE_SPECIFIC_DRAFTS,
            ['draft', $this->record['id'], BL::getWorkingLanguage()]
        );

        // hide columns
        $this->dgDrafts->setColumnsHidden(['id', 'revision_id']);

        // disable paging
        $this->dgDrafts->setPaging(false);

        // set headers
        $this->dgDrafts->setHeaderLabels([
            'user_id' => \SpoonFilter::ucfirst(BL::lbl('By')),
            'edited_on' => \SpoonFilter::ucfirst(BL::lbl('LastEditedOn')),
        ]);

        // set column-functions
        $this->dgDrafts->setColumnFunction(
            [new BackendDataGridFunctions(), 'getUser'],
            ['[user_id]'],
            'user_id'
        );
        $this->dgDrafts->setColumnFunction(
            [new BackendDataGridFunctions(), 'getTimeAgo'],
            ['[edited_on]'],
            'edited_on'
        );

        // our JS needs to know an id, so we can highlight it
        $this->dgDrafts->setRowAttributes(['id' => 'row-[revision_id]']);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Edit')) {
            // set column URLs
            $this->dgDrafts->setColumnURL(
                'title',
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]&amp;draft=[revision_id]'
            );

            // add use column
            $this->dgDrafts->addColumn(
                'use_draft',
                null,
                BL::lbl('UseThisDraft'),
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]&amp;draft=[revision_id]',
                BL::lbl('UseThisDraft')
            );
        }
    }

    private function loadForm(): void
    {
        // create form
        $this->frm = new BackendForm('edit');

        // set hidden values
        $rbtHiddenValues = [
            ['label' => BL::lbl('Hidden', $this->url->getModule()), 'value' => 'Y'],
            ['label' => BL::lbl('Published'), 'value' => 'N'],
        ];

        // get categories
        $categories = BackendBlogModel::getCategories();
        $categories['new_category'] = \SpoonFilter::ucfirst(BL::getLabel('AddCategory'));

        // create elements
        $this->frm->addText('title', $this->record['title'], null, 'form-control title', 'form-control danger title');
        $this->frm->addEditor('text', $this->record['text']);
        $this->frm->addEditor('introduction', $this->record['introduction']);
        $this->frm->addRadiobutton('hidden', $rbtHiddenValues, $this->record['hidden']);
        $this->frm->addCheckbox('allow_comments', ($this->record['allow_comments'] === 'Y' ? true : false));
        $this->frm->addDropdown('category_id', $categories, $this->record['category_id']);
        if (count($categories) != 2) {
            $this->frm->getField('category_id')->setDefaultElement('');
        }
        $this->frm->addDropdown('user_id', BackendUsersModel::getUsers(), $this->record['user_id']);
        $this->frm->addText(
            'tags',
            BackendTagsModel::getTags($this->url->getModule(), $this->record['id']),
            null,
            'form-control js-tags-input',
            'form-control danger js-tags-input'
        );
        $this->frm->addDate('publish_on_date', $this->record['publish_on']);
        $this->frm->addTime('publish_on_time', date('H:i', $this->record['publish_on']));
        if ($this->imageIsAllowed) {
            $this->frm->addImage('image');
            $this->frm->addCheckbox('delete_image');
        }

        // meta object
        $this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);

        // set callback for generating a unique URL
        $this->meta->setUrlCallback('Backend\Modules\Blog\Engine\Model', 'getUrl', [$this->record['id']]);
    }

    private function loadRevisionDataGrid(): void
    {
        // create datagrid
        $this->dgRevisions = new BackendDataGridDatabase(
            BackendBlogModel::QRY_DATAGRID_BROWSE_REVISIONS,
            ['archived', $this->record['id'], BL::getWorkingLanguage()]
        );

        // hide columns
        $this->dgRevisions->setColumnsHidden(['id', 'revision_id']);

        // disable paging
        $this->dgRevisions->setPaging(false);

        // set headers
        $this->dgRevisions->setHeaderLabels([
            'user_id' => \SpoonFilter::ucfirst(BL::lbl('By')),
            'edited_on' => \SpoonFilter::ucfirst(BL::lbl('LastEditedOn')),
        ]);

        // set column-functions
        $this->dgRevisions->setColumnFunction(
            [new BackendDataGridFunctions(), 'getUser'],
            ['[user_id]'],
            'user_id'
        );
        $this->dgRevisions->setColumnFunction(
            [new BackendDataGridFunctions(), 'getTimeAgo'],
            ['[edited_on]'],
            'edited_on'
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('Edit')) {
            // set column URLs
            $this->dgRevisions->setColumnURL(
                'title',
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]&amp;revision=[revision_id]'
            );

            // add use column
            $this->dgRevisions->addColumn(
                'use_revision',
                null,
                BL::lbl('UseThisVersion'),
                BackendModel::createUrlForAction('Edit') . '&amp;id=[id]&amp;revision=[revision_id]',
                BL::lbl('UseThisVersion')
            );
        }
    }

    protected function parse(): void
    {
        parent::parse();

        // get url
        $url = BackendModel::getUrlForBlock($this->url->getModule(), 'detail');
        $url404 = BackendModel::getUrl(404);

        // parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }

        // fetch proper slug
        $this->record['url'] = $this->meta->getUrl();

        // assign the active record and additional variables
        $this->tpl->assign('item', $this->record);
        $this->tpl->assign('status', BL::lbl(\SpoonFilter::ucfirst($this->record['status'])));

        // assign revisions-datagrid
        $this->tpl->assign('revisions', ($this->dgRevisions->getNumResults() != 0) ? $this->dgRevisions->getContent() : false);
        $this->tpl->assign('drafts', ($this->dgDrafts->getNumResults() != 0) ? $this->dgDrafts->getContent() : false);

        $this->tpl->assign('imageIsAllowed', $this->imageIsAllowed);

        // assign category
        if ($this->categoryId !== null) {
            $this->tpl->assign('categoryId', $this->categoryId);
        }
    }

    private function validateForm(): void
    {
        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // get the status
            $status = $this->getRequest()->request->get('status');
            if (!in_array($status, ['active', 'draft'])) {
                $status = 'active';
            }

            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));
            $this->frm->getField('text')->isFilled(BL::err('FieldIsRequired'));
            $this->frm->getField('publish_on_date')->isValid(BL::err('DateIsInvalid'));
            $this->frm->getField('publish_on_time')->isValid(BL::err('TimeIsInvalid'));
            $this->frm->getField('category_id')->isFilled(BL::err('FieldIsRequired'));

            // validate meta
            $this->meta->validate();

            // no errors?
            if ($this->frm->isCorrect()) {
                // build item
                $item = [
                    'id' => $this->id,
                    'meta_id' => $this->meta->save(),
                    'revision_id' => $this->record['revision_id'],
                    'category_id' => (int) $this->frm->getField('category_id')->getValue(),
                    'user_id' => $this->frm->getField('user_id')->getValue(),
                    'language' => BL::getWorkingLanguage(),
                    'title' => $this->frm->getField('title')->getValue(),
                    'introduction' => $this->frm->getField('introduction')->getValue(),
                    'text' => $this->frm->getField('text')->getValue(),
                    'publish_on' => BackendModel::getUTCDate(
                        null,
                        BackendModel::getUTCTimestamp(
                            $this->frm->getField('publish_on_date'),
                            $this->frm->getField('publish_on_time')
                        )
                    ),
                    'edited_on' => BackendModel::getUTCDate(),
                    'hidden' => $this->frm->getField('hidden')->getValue(),
                    'allow_comments' => $this->frm->getField('allow_comments')->getChecked() ? 'Y' : 'N',
                    'status' => $status,
                ];
                if ($this->imageIsAllowed) {
                    $item['image'] = $this->record['image'];

                    // the image path
                    $imagePath = FRONTEND_FILES_PATH . '/blog/images';

                    // create folders if needed
                    $filesystem = new Filesystem();
                    $filesystem->mkdir([$imagePath . '/source', $imagePath . '/128x128']);

                    // If the image should be deleted, only the database entry is refreshed.
                    // The revision should keep its file.
                    if ($this->frm->getField('delete_image')->isChecked()) {
                        // reset the name
                        $item['image'] = null;
                    }

                    // new image given?
                    if ($this->frm->getField('image')->isFilled()) {
                        // build the image name
                        // we use the previous revision-id in the filename to make the filename unique between
                        // the different revisions, to prevent that a new file would
                        // overwrite images of previous revisions that have the same title, and thus, the same filename
                        $item['image'] = $this->meta->getUrl() .
                                         '-' . BL::getWorkingLanguage() .
                                            '-' . $item['revision_id'] .
                                            '.' . $this->frm->getField('image')->getExtension();

                        // upload the image & generate thumbnails
                        $this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
                    } elseif ($item['image'] != null) {
                        // generate the new filename
                        $image = new File($imagePath . '/source/' . $item['image']);
                        $newName = $this->meta->getUrl() .
                                   '-' . BL::getWorkingLanguage() .
                                            '-' . $item['revision_id'] .
                                            '.' . $image->getExtension();

                        // extract the filenames excluding …-[language]-[revision-id].jpg
                        // to properly compare them to eachother
                        $regex = '/(.*)-[a-z]{2}-[0-9]+\.(.*)/';

                        // only copy if the new name differs from the old filename
                        if (preg_replace($regex, '$1', $newName) != preg_replace($regex, '$1', $item['image'])) {
                            // loop folders
                            foreach (BackendModel::getThumbnailFolders($imagePath, true) as $folder) {
                                $filesystem->copy($folder['path'] . '/' . $item['image'], $folder['path'] . '/' . $newName);
                            }

                            // assign the new name to the database
                            $item['image'] = $newName;
                        }
                    }
                } else {
                    $item['image'] = null;
                }

                // update the item
                $item['revision_id'] = BackendBlogModel::update($item);

                // recalculate comment count so the new revision has the correct count
                BackendBlogModel::reCalculateCommentCount([$this->id]);

                // save the tags
                BackendTagsModel::saveTags(
                    $item['id'],
                    $this->frm->getField('tags')->getValue(),
                    $this->url->getModule()
                );

                // active
                if ($item['status'] == 'active') {
                    // edit search index
                    BackendSearchModel::saveIndex(
                        $this->getModule(),
                        $item['id'],
                        ['title' => $item['title'], 'text' => $item['text']]
                    );

                    // build URL
                    $redirectUrl = BackendModel::createUrlForAction('Index') .
                                   '&report=edited&var=' . rawurlencode($item['title']) .
                                   '&id=' . $this->id . '&highlight=row-' . $item['revision_id'];
                } elseif ($item['status'] == 'draft') {
                    // draft: everything is saved, so redirect to the edit action
                    $redirectUrl = BackendModel::createUrlForAction('Edit') .
                                   '&report=saved-as-draft&var=' . rawurlencode($item['title']) .
                                   '&id=' . $item['id'] . '&draft=' . $item['revision_id'] .
                                   '&highlight=row-' . $item['revision_id'];
                }

                // append to redirect URL
                if ($this->categoryId != null) {
                    $redirectUrl .= '&category=' . $this->categoryId;
                }

                // everything is saved, so redirect to the overview
                $this->redirect($redirectUrl);
            }
        }
    }

    private function loadDeleteForm(): void
    {
        $deleteForm = $this->createForm(
            BlogDeleteType::class,
            ['id' => $this->record['id'], 'categoryId' => $this->categoryId],
            ['module' => $this->getModule()]
        );
        $this->tpl->assign('deleteForm', $deleteForm->createView());
    }
}
