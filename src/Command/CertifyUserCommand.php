<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:certify-user',
    description: 'Certify the user',
)]
class CertifyUserCommand extends Command
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user', InputArgument::REQUIRED, 'User to certify')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('user');

        $user = $this->userRepository->findOneBy(['username' => $arg1]);

         if (empty($user)) {
             $io->error('User not found !');

             return Command::FAILURE;
         }

         if (in_array('ROLE_CERTIFIED_USER', $user->getRoles())) {
             $prompt = $io->ask($user->getUserIdentifier() . " has already been certified, do you want to uncertified it ?", "yes");

             if ($prompt === "yes") {
                 $user->resetRoles();
                 $this->userRepository->save($user, true);

                 $io->success("The user " . $user->getUserIdentifier() . " has been uncertified");

                 return Command::SUCCESS;
             } else {
                 $io->error('Cancel');

                 return Command::FAILURE;
             }
         }

         $user->setRoles((array)'ROLE_CERTIFIED_USER');
         $this->userRepository->save($user, true);

        $io->success("The user " . $user->getUserIdentifier() . " has been certified");

        return Command::SUCCESS;
    }
}
