<?php

namespace App\Command\migration;

use App\Entity\admin\utilisateur\Profil;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\ProfilPage;
use App\Entity\admin\ProfilAgenceService;
use App\Repository\ApplicationRepository;
use App\Repository\PageRepository;
use App\Repository\AgenceServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProfilMigrationCommand extends Command
{
    protected static $defaultName = 'app:migrate-profils';

    private EntityManagerInterface $em;
    private ApplicationRepository $appRepo;
    private PageRepository $pageRepo;
    private AgenceServiceRepository $agServRepo;

    public function __construct(
        EntityManagerInterface $em,
        ApplicationRepository $appRepo,
        PageRepository $pageRepo,
        AgenceServiceRepository $agServRepo
    ) {
        parent::__construct();
        $this->em = $em;
        $this->appRepo = $appRepo;
        $this->pageRepo = $pageRepo;
        $this->agServRepo = $agServRepo;
    }

    protected function configure(): void
    {
        $this->setDescription('Migre tous les profils, leurs applications, pages et agences/services.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $profilsData = include __DIR__ . '/profils_data.php'; // ton array JSON ou PHP

        $io->title('Migration des profils');

        foreach ($profilsData as $profilData) {
            $profil = new Profil();
            $profil->setRefProfil($profilData['ref']);
            $profil->setDesignation($profilData['designation']);
            $profil->setSocieteId($profilData['societe_id']);
            $profil->setDateCreation(new \DateTime());
            $profil->setDateModification(new \DateTime());

            $this->em->persist($profil);
            $io->text("Profil {$profilData['ref']} créé.");

            foreach ($profilData['applications'] as $appData) {
                $application = $this->appRepo->findOneBy(['codeApp' => $appData['code_app']]);
                if (!$application) {
                    $io->warning("Application {$appData['code_app']} non trouvée. Ignorée.");
                    continue;
                }

                // Association profil ↔ application
                $appProfil = new ApplicationProfil();
                $appProfil->setApplication($application);
                $appProfil->setProfil($profil);
                $this->em->persist($appProfil);

                // Pages existantes
                foreach ($appData['pages'] as $nomRoute) {
                    $page = $this->pageRepo->findOneBy(['nomRoute' => $nomRoute, 'application' => $application]);
                    if (!$page) continue;

                    $profilPage = new ProfilPage();
                    $profilPage->setApplicationProfil($appProfil);
                    $profilPage->setPage($page);
                    $profilPage->setPeutVoir(true); // tu peux adapter selon tes règles
                    $this->em->persist($profilPage);
                }

                // Agences / services existants
                foreach ($appData['agences_services'] as $as) {
                    $agServ = $this->agServRepo->findByAgenceServiceCodes($as['code_agence'], $as['code_service']);
                    foreach ($agServ as $asEntity) {
                        $profilAgServ = new ProfilAgenceService();
                        $profilAgServ->setApplicationProfil($appProfil);
                        $profilAgServ->setAgenceService($asEntity);
                        $this->em->persist($profilAgServ);
                    }
                }
            }
        }

        $this->em->flush();
        $io->success('Migration terminée avec succès.');

        return Command::SUCCESS;
    }
}
