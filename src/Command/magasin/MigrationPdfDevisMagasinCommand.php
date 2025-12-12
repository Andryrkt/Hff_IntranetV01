<?php

namespace App\Service\genererPdf\magasin\devis;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationPdfDevisMagasinCommand extends Command
{
    protected static $defaultName = 'app:migration-pdf-devis-magasin';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migration des pdfs devis magasin.')
            ->setHelp('Cette commande vous permet de migrer les pdfs devis magasin...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdfMigrationDevisMagasinVpService = new MigrationPdfDevisMagasinService($this->em);
        $pdfMigrationDevisMagasinVpService->migrationPdfDevisMagasin($output);
        return Command::SUCCESS;
    }
}
