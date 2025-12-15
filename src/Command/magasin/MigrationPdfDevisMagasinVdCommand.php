<?php

namespace App\Command\magasin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\migration\magasin\MigrationPdfDevisMagasinVdService;

class MigrationPdfDevisMagasinVdCommand extends Command
{
    protected static $defaultName = 'app:migration-pdf-devis-magasin-vd';

    protected function configure()
    {
        $this
            ->setDescription('Migration des pdfs devis magasin. ligne de commande "php -d memory_limit=1024M bin/console app:migration-pdf-devis-magasin-vd"')
            ->setHelp('Cette commande vous permet de migrer les pdfs devis magasin pour validation devis...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdfMigrationDevisMagasinVdService = new MigrationPdfDevisMagasinVdService();
        $pdfMigrationDevisMagasinVdService->migrationPdfDevisMagasin($output);
        return Command::SUCCESS;
    }
}
