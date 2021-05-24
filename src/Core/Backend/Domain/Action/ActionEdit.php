<?php

namespace ForkCMS\Core\Backend\Domain\Action;

use ForkCMS\Core\Backend\Domain\DataGrid\DataGridDatabase;
use ForkCMS\Core\Backend\Domain\Form\Form;
use ForkCMS\Core\Backend\Domain\Meta\Meta;

/**
 * This class implements a lot of functionality that can be extended by the real action.
 * In this case this is the base class for the edit action
 */
class ActionEdit extends Action
{
    /**
     * DataGrid with the revisions
     *
     * @var DataGridDatabase
     */
    protected $dgRevisions;

    /**
     * The form instance
     *
     * @var Form
     */
    protected $form;

    /**
     * The id of the item to edit
     *
     * @var int
     */
    protected $id;

    /**
     * The backend meta-object
     *
     * @var Meta
     */
    protected $meta;

    /**
     * The data of the item to edit
     *
     * @var array
     */
    protected $record;

    protected function parse(): void
    {
        parent::parse();

        if ($this->form) {
            $this->form->parse($this->template);
        }
    }
}
