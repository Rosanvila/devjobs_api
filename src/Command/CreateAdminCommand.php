<?php

namespace App\Command;

use App\Service\AuthService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur par défaut',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private AuthService $authService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $admin = $this->authService->createUser(
                'admin@devjobs.com',
                'admin123',
                ['ROLE_USER', 'ROLE_ADMIN']
            );

            $io->success(sprintf(
                'Admin créé : %s (%s)',
                $admin->getEmail(),
                implode(', ', $admin->getRoles())
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
