<?php

namespace App\Command\magasin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\migration\magasin\MigrationPdfDevisMagasinVpService;

class MigrationPdfDevisMagasinVpCommand extends Command
{
    protected static $defaultName = 'app:migration-pdf-devis-magasin-vp';

    protected function configure()
    {
        $this
            ->setDescription('Migration des pdfs devis magasin. ligne de commande "php -d memory_limit=1024M bin/console app:migration-pdf-devis-magasin-vp"')
            ->setHelp('Cette commande vous permet de migrer les pdfs devis magasin pour verification de prix...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdfMigrationDevisMagasinVpService = new MigrationPdfDevisMagasinVpService();
        $pdfMigrationDevisMagasinVpService->migrationPdfDevisMagasin($output);
        return Command::SUCCESS;
    }
}
