<?php

namespace ForkCMS\Modules\Backend\Domain\User;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, mixed> */
final class SuperAdminVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, 'ROLE_MODULE_WIDGET__')
               || str_starts_with($attribute, 'ROLE_MODULE_ACTION__')
               || str_starts_with($attribute, 'ROLE_MODULE_AJAX_ACTION__')
               || str_starts_with($attribute, 'ROLE_MODULE__');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }
        if (!$user->hasAccessToBackend()) {
            $vote?->addReason('The user does not have access to the backend.');
            return false;
        }
        if (!$user->isSuperAdmin()) {
            $vote?->addReason('The user is not a super administrator.');
            return false;
        }

        return true;
    }
}
