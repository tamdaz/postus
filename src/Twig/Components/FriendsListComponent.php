<?php

namespace App\Twig\Components;

use App\Entity\Conversation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\UX\LiveComponent\{DefaultActionTrait, ValidatableComponentTrait};
use App\Repository\{UserRepository, FollowerRepository, ConversationRepository};
use Symfony\UX\LiveComponent\Attribute\{AsLiveComponent, LiveAction, LiveArg, LiveProp};

#[AsLiveComponent('friends_list')]
final class FriendsListComponent
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp]
    public int $userId;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public array $usersSelected = [];

    #[LiveProp(writable: true)]
    public string $errMessage = "";

    public function __construct(
        protected UserRepository $userRepository,
        protected FollowerRepository $followerRepository,
        protected ConversationRepository $conversationRepository
    ) {}

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
    public function create(Conversation $conversation): HttpException|RedirectResponse|null
    {
        if (!empty($this->usersSelected)) {
            $this->usersSelected[] = $this->userId;

            $conv = $this->conversationRepository->findByUsers(
                $this->userRepository->find($this->usersSelected[0]),
                $this->userRepository->find($this->usersSelected[1])
            );

            if ($conv) {
                $this->usersSelected = [];

                $this->errMessage = "This conversation already exists";

                return null;
            }

            if (count($this->usersSelected) > 2) {
                $conversation->setOwner($this->userRepository->find($this->userId));
            }

            asort($this->usersSelected);

            foreach ($this->usersSelected as $id) {
                $conversation->addUser($this->userRepository->find($id));
            }

            $this->conversationRepository->save($conversation, true);

            return new RedirectResponse("/conversation/" . $conversation->getId()->jsonSerialize());
        } else {
            $this->usersSelected = [];

            $this->errMessage = "No user(s) selected";

            return null;
        }
    }
}
