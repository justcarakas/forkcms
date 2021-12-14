<?php

namespace ForkCMS\Modules\Backend\Backend\Actions;

use Doctrine\ORM\QueryBuilder;
use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\Backend\Domain\User\User;
use Symfony\Component\HttpFoundation\Request;

/**
 *Overview of the available users in the backend
 */
final class UserIndex extends AbstractActionController
{
    protected function execute(Request $request): void
    {
        $this->assign(
            'userDataGrid',
            $this->dataGridFactory->forEntity(User::class, static function (QueryBuilder $queryBuilder): void {
                $queryBuilder
                    ->andWhere( 'User.deletedAt IS NULL');
            })
        );
    }
}
