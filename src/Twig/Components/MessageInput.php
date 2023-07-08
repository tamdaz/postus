<?php

namespace App\Twig\Components;

use App\Entity\Message;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\{UserRepository, MessageRepository, ConversationRepository};
use Symfony\UX\LiveComponent\Attribute\{LiveProp, LiveAction, AsLiveComponent};

#[AsLiveComponent('message_input')]
final class MessageInput
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $uuid;

    #[LiveProp]
    public int $userId;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        protected ConversationRepository $conversationRepository,
        protected MessageRepository $messageRepository,
        protected UserRepository $userRepository
    ) {}

    #[LiveAction]
    public function sendMessage(Message $message, HubInterface $hub): void
    {
        $user = $this->userRepository->find($this->userId);

        $message
            ->setText($this->query)
            ->setUser($user)
            ->setConversation($this->conversationRepository->find($this->uuid));

        $this->messageRepository->save($message, true);

        $update = new Update("https://postus.fr/conversation/" . $this->uuid, json_encode([
            'username' => $user->getUserIdentifier(),
            'message' => $this->query
        ]));

        $hub->publish($update);

        $this->query = "";
    }
}
