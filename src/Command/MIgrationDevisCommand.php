<?php

namespace App\Command;

use App\Service\migration\MigrationDevisService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationDevisCommand extends Command
{
    protected static $defaultName = 'app:migration-devis';

    protected function configure()
    {
        $this
            ->setDescription('Migration des devis. voici la ligne de commande pour faire fonctionner : "php bin/console app:migration-devis" et les données excel sont récupérer "C:\wamp64\www\Hffintranet\src\Service\migration\migration_devi.xlsx" pour la ligne "2" et le colonne "B"')
            ->setHelp('Cette commande vous permet de migrer les devis dit...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationDevis = new MigrationDevisService();
        $migrationDevis->migrationDevis($input, $output);
        return Command::SUCCESS;
    }
}