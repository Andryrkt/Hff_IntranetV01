<?php

namespace App\Command\magasin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\migration\magasin\MigrationPdfDevisMagasinVdService;

class MigrationPdfDevisMagasinVdCommand extends Command
{
    protected static $defaultName = 'app:migration-pdf-devis-magasin-vd';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migration des pdfs devis magasin. ligne de commande "php -d memory_limit=1024M bin/console app:migration-pdf-devis-magasin-vd"')
            ->setHelp('Cette commande vous permet de migrer les pdfs devis magasin pour validation devis...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdfMigrationDevisMagasinVdService = new MigrationPdfDevisMagasinVdService($this->em);
        $pdfMigrationDevisMagasinVdService->migrationPdfDevisMagasin($output);
        return Command::SUCCESS;
    }
}
