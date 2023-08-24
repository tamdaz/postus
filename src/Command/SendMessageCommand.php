<?php

namespace App\Command;

use App\Entity\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Mercure\{HubInterface, Update};
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\{ConversationRepository, MessageRepository, UserRepository};

#[AsCommand(
    name: 'app:send-message',
    description: 'Send a message to the specific conversation',
)]
class SendMessageCommand extends Command
{
    public function __construct(
        protected HubInterface $hub,
        protected UserRepository $userRepository,
        protected MessageRepository $messageRepository,
        protected ConversationRepository $conversationRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        dump($this->conversationRepository->findAll());

        [$uuid, $user, $message] = [
            $io->ask("Specify the UUID conversation ?"),
            $io->ask("Which user do you want to send a message ?"),
            $io->ask("Message input")
        ];

        $conversation = $this->conversationRepository->find($uuid);

        if (!uuid_is_valid($uuid)) {
            $io->error("The inserted UUID is not valid");

            return Command::INVALID;
        }

        if (!$conversation) {
            $io->error("Conversation not found");

            return Command::FAILURE;
        }

        if (empty($uuid) || empty($user) || empty($message)) {
            $io->error('One or more inputs is empty, try again');

            return Command::FAILURE;
        }

        $msg = (new Message())
            ->setText($message)
            ->setUser($this->userRepository->findOneBy(
                ['username' => $user]
            ));

        $update = new Update("https://postus.fr/conversation/" . $uuid, json_encode([
            'username' => $this->userRepository->findOneBy(
                ['username' => $user]
            )->getUserIdentifier(),
            'message' => $message
        ]));

        $this->hub->publish($update);

        $this->messageRepository->save($msg, true);
        $conversation->addMessage($msg);

        $io->success('Message sent !');

        return Command::SUCCESS;
    }
}
