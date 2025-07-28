<?php

namespace App\Command;

use App\Service\migration\MigrationPdfDitService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationPdfCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:migration-pdf';

    private $em;

    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        parent::__construct();
        $this->em = $em;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migration des pdfs. voici la ligne de commande pour faire fonctionner : "php -d memory_limit=1024M bin/console app:migration-pdf"')
            ->setHelp('Cette commande vous permet de migrer les pdfs dit...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationPdfDitService = new MigrationPdfDitService($this->em, $this->logger);
        $migrationPdfDitService->migrationPdfDit($output);

        return Command::SUCCESS;
    }
}
