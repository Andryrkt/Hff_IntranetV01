<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Service\migration\MigrationPdfDitService;
use App\Model\dit\DitModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationPdfCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:migration-da';

    private $em;
    private DitModel $ditModel;

    public function __construct(EntityManagerInterface $em, DitModel $ditModel)
    {
        parent::__construct();
        $this->em = $em;
        $this->ditModel = $ditModel;
    }

    protected function configure()
    {
        $this
            ->setDescription('Migration des da_valider, dal, dalr, da dans da_afficher. voici la ligne de commande pour faire fonctionner : "php bin/console app:migration-da"')
            ->setHelp('Cette commande vous permet de migrer les pdfs dit...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationPdfDitService = new MigrationPdfDitService($this->em, $this->ditModel);
        $migrationPdfDitService->migrationPdfDit($output);
        return Command::SUCCESS;
    }
}
