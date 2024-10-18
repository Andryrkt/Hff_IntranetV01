<?php

namespace App\Command;

use App\Entity\dit\AncienDit;
use App\Controller\Controller;
use App\Service\AncienDitService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AncienDitCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:my-command';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Une commande exemple.')
            ->setHelp('Cette commande vous permet de faire quelque chose...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ancienDit = new AncienDitService($this->em);
        $repository = $this->em->getRepository(AncienDit::class);
        //$count = $repository->count([]);
        $numDit = $repository->findAllNumeroDit();
        
        $total = count($numDit);
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();
        // Traitement des données
        //$output->writeln('Traitement des données...');
        for ($i = 0; $i < $total; $i++) {
        
            $ancienDit->recupDesAncienDonnee($numDit[$i]);
            // Avancer la barre de progression d'une étape
            $progressBar->advance();
    
        }
        // Afficher le nombre de résultats
        $output->writeln("\nNombre de résultats : " . count($numDit));
        $progressBar->finish();
        $output->writeln("\nTerminé !");
        return Command::SUCCESS;
    }
}
