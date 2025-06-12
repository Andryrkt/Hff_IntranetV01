<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Service\migration\MigrationDataDitService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationDataCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:migration-data';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migration des data dit.')
            ->setHelp('Cette commande vous permet de migrer les data dit...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationPdfDitService = new MigrationDataDitService($this->em);
        $migrationPdfDitService->migrationDataDit($output);
        return Command::SUCCESS;
    }
}