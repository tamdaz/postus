<?php

namespace App\Twig\Components;

use App\Service\ConversationService;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\{UserRepository, FollowerRepository, ConversationRepository};
use Symfony\UX\LiveComponent\Attribute\{AsLiveComponent, LiveAction, LiveArg, LiveProp};

#[AsLiveComponent('friends_list')]
final class FriendsListComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $userId;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public array $usersSelected = [];

    #[LiveProp(writable: true)]
    public string $errMessage = "";

    public function __construct(
        protected UserRepository         $userRepository,
        protected FollowerRepository     $followerRepository,
        protected ConversationService    $conversationService,
        protected ConversationRepository $conversationRepository,
    ) {}

    /**
     * Get list of followed users (only for debug)
     *
     * @return array
     */
    #[LiveAction]
    public function getUsers(): array
    {
        return $this->userRepository->findFollowedUser($this->userId, $this->query);
    }

    #[LiveAction]
    public function toggle(#[LiveArg] int $id): void
    {
        if (!in_array($id, $this->usersSelected)) {
            $this->usersSelected[] = $id;
        } else {
            unset($this->usersSelected[array_search($id, $this->usersSelected)]);
        }
    }

    #[LiveAction]
    public function create(): RedirectResponse|null
    {
        $this->usersSelected[] = $this->userId;

        if (count($this->usersSelected) === 1) {
            $this->usersSelected = [];
            $this->errMessage = "No user(s) selected";

            return null;
        }

        if (count($this->usersSelected) === 2) {
            if ($this->conversationService->isExists($this->usersSelected)) {
                $this->usersSelected = [];

                $this->errMessage = "Conversation already exists";

                return null;
            }
        }

        return $this->conversationService->create($this->usersSelected, $this->userId);
    }
}
