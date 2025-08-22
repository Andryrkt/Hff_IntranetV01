<?php

namespace App\Controller;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

class MigrationDaController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
    }


    /**
     * @Route("/migration-da", name="migr_da")
     *
     * @return void
     */
    public function migreDa()
    {
        $numeroDaDemandeAchat = [
            'DAP25079983',
            'DAP25089982',
            'DAP25089990',
            'DAP25089981',
            'DAP25089980',
            'DAP25089995'
        ];

        $numeroDaPropositionAchat = [
            'DAP25089985',
            'DAP25089984',
            'DAP25089983'
        ];

        foreach ($numeroDaPropositionAchat as  $numDa) {
            $this->ajouterDansTableAffichageParNumDa($numDa);
        }
    }

    public function ajouterDansTableAffichageParNumDa(string $numDa): void
    {
        $em = self::$em;

        /** @var DemandeAppro $demandeAppro la DA correspondant au numero DA $numDa */
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $donneesAfficher = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);
        foreach ($donneesAfficher as $donneeAfficher) {
            $daAfficher = new DaAfficher();
            if ($demandeAppro->getDit()) {
                $daAfficher->setDit($demandeAppro->getDit());
            }
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->setNumeroVersion(1);
            if ($donneeAfficher instanceof DemandeApproL) {
                $daAfficher->enregistrerDal($donneeAfficher); // enregistrement pour DAL
            } else if ($donneeAfficher instanceof DemandeApproLR) {
                $daAfficher->enregistrerDalr($donneeAfficher); // enregistrement pour DALR
            }

            $em->persist($daAfficher);
        }
        $em->flush();
    }

    private function getLignesRectifieesDA(string $numeroDA, int $version): array
    {
        // 1. Récupération des lignes DAL (non supprimées)
        /** @var iterable<DemandeApproL> les lignes de DAL non supprimées */
        $lignesDAL = $this->demandeApproLRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
            'numeroVersion'      => $version,
            'deleted'            => false,
        ]);

        // 2. Récupération en une seule requête des DALR associés à la DA
        /** @var iterable<DemandeApproLR> les lignes de DALR correspondant au numéro de la DA */
        $dalrs = $this->demandeApproLRRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
        ]);

        // 3. Indexation des DALR par numéro de ligne, uniquement s'ils sont validés (choix = true)
        $dalrParLigne = [];

        foreach ($dalrs as $dalr) {
            if ($dalr->getChoix()) {
                $dalrParLigne[$dalr->getNumeroLigne()] = $dalr;
            }
        }

        // 4. Construction de la liste finale en remplaçant les DAL par DALR si dispo
        $resultats = [];

        foreach ($lignesDAL as $ligneDAL) {
            $numeroLigne = $ligneDAL->getNumeroLigne(); // numéro de ligne de la DAL
            $resultats[] = $dalrParLigne[$numeroLigne] ?? $ligneDAL;
        }

        return $resultats;
    }
}
