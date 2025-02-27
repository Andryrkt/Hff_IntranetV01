<?php

namespace App\Command\dit;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\dit\transfer\TraitementAncienDitService;

class TransferDataDitExterneCommand extends Command
{
    protected static $defaultName = 'app:dit-externe';

    private $traitementAncienDitService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->traitementAncienDitService = new TraitementAncienDitService($entityManager);
    }

    protected function configure()
    {
        $this
            ->setDescription('Transfer des anciens dit client externe dans l\'ancien Intranet vers le nouveau intranet')
            ->setHelp('Cette commande vous permet de transferer les ancien donnée dit dans l\'ancien Intranet vers le nouveau intranet');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Début du traitement des anciens DIT...');

        // Obtenir le nombre total d'éléments à traiter
        $total = $this->traitementAncienDitService->getNombreElementsDit();

        // Initialiser la barre de progression
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();

        // Appeler le service et lui passer la barre de progression
        $this->traitementAncienDitService->traitementDit($progressBar);

        // Finir la barre
        $progressBar->finish();
        $output->writeln("\nTraitement terminé !");
        return Command::SUCCESS;
    }
}
