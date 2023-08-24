<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PostVoter extends Voter
{
    public const VIEW = 'POST_VIEW';
    public const EDIT = 'POST_EDIT';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT => $this->canEdit($user, $post),
            self::DELETE => $this->canDelete($user, $post),
            default => false,
        };
    }

    private function canEdit(UserInterface $user, Post $post): bool
    {
        return $user->getUserIdentifier() === $post->getAuthor()->getUserIdentifier();
    }

    private function canDelete(UserInterface $user, Post $post): bool
    {
        return $user->getUserIdentifier() === $post->getAuthor()->getUserIdentifier();
    }
}
