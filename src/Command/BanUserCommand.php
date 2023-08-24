<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};

#[AsCommand(
    name: 'app:ban-user',
    description: 'Ban/unban a user',
)]
class BanUserCommand extends Command
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user', InputArgument::REQUIRED, 'User target')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('user');

        $user = $this->userRepository->findOneBy([
            'username' => $arg1
        ]);

        if (empty($user)) {
            $io->error("User not found");

            return Command::FAILURE;
        }

        $user->setIsBanned(!$user->isBanned());
        $this->userRepository->save($user, true);

        if ($user->isBanned()) {
            $io->success("{$user->getUserIdentifier()} has been banned !");
        } else {
            $io->success("{$user->getUserIdentifier()} has been unbanned !");
        }

        return Command::SUCCESS;
    }
}
