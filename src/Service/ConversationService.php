<?php

namespace App\Service;

use App\Entity\Conversation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\{ConversationRepository, UserRepository};

class ConversationService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected ConversationRepository $conversationRepository
    ) {}

    /**
     * Create a conversation
     *
     * @param array $usersSelected
     * @param int $ownerId
     *
     * @return RedirectResponse
     */
    public function create(array $usersSelected, int $ownerId): RedirectResponse
    {
        $conversation = new Conversation();

        if (count($usersSelected) > 2) {
            $conversation->setOwner($this->userRepository->find($ownerId));
        }

        foreach ($usersSelected as $id) {
            $conversation->addUser($this->userRepository->find($id));
        }

        $this->conversationRepository->save($conversation, true);

        return new RedirectResponse('/conversation/' . $conversation->getId());
    }

    /**
     * Check if the specified conversation exists or not
     *
     * @param array $usersSelected
     * @return bool
     */
    public function isExists(array $usersSelected): bool
    {
        $conv = $this->conversationRepository->findByUsers(
            $this->userRepository->find($usersSelected[0]),
            $this->userRepository->find($usersSelected[1])
        );

        return (bool) $conv;
    }
}