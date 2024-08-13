<?php

namespace App\Command;

use App\Controller\Controller;
use App\Entity\AncienDit;
use App\Service\AncienDitService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AncienDitCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:my-command';


    protected function configure()
    {
        $this
            ->setDescription('Une commande exemple.')
            ->setHelp('Cette commande vous permet de faire quelque chose...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Supposons que vous ayez une collection de données à insérer
        //$data = $this->em->getRepository(AncienDit::class)->findAll(); // Méthode qui retourne les données
     
        // $total = count($data);
       
        // $progressBar = new ProgressBar($output, $total);
        // $progressBar->start();

        // $progressBar->finish();

        $ancienDit = new AncienDitService();

        dd($ancienDit->recupDesAncienDonnee());
        $output->writeln("\nTerminé !");
        return Command::SUCCESS;
    }
}
