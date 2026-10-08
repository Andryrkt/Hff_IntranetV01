<?php

namespace App\Service\da;

use DateTime;
use App\Constants\da\StatutDaConstant;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\ExcelService;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;

class DaValidationService
{
    private EntityManagerInterface $em;
    private UserDataService $userDataService;
    private DaService $daService;
    private DaAfficherService $daAfficherService;
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(
        EntityManagerInterface $em,
        UserDataService $userDataService,
        DaService $daService,
        DaAfficherService $daAfficherService,
        DaSoumissionValidationService $daSoumissionValidationService
    ) {
        $this->em = $em;
        $this->userDataService = $userDataService;
        $this->daService = $daService;
        $this->daAfficherService = $daAfficherService;
        $this->daSoumissionValidationService = $daSoumissionValidationService;
    }

    /**
     * Modification des tables DemandeAppro, DemandeApproL et DemandeApproLR
     * (le flush est fait plus tard par l'appelant)
     */
    public function validerDemandeApproAvecLignes(string $numDa, int $numeroVersion, array $prixUnitaire = [], array $refsValide = []): ?DemandeAppro
    {
        $user = $this->userDataService->getUser();
        $nomutilisateur = $user->getNomUtilisateur();

        /** @var DemandeAppro|null $da */
        $da = $this->em->getRepository(DemandeAppro::class)->findOneBy(['numeroDemandeAppro' => $numDa]);

        if (!$da) return null;

        // 1. Mise à jour de la DA
        $da
            ->setEstValidee(true)
            ->setValidateur($user)
            ->setValidePar($nomutilisateur)
            ->setStatutDal(StatutDaConstant::STATUT_VALIDE);
        $this->em->persist($da);

        // 2. Mise à jour des lignes DAL
        /** @var iterable<DemandeApproL> $dals les lignes de DAL dernière version */
        $dals = $this->em->getRepository(DemandeApproL::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersion]);
        foreach ($dals as $dal) {
            $dal
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(StatutDaConstant::STATUT_VALIDE);

            if (isset($prixUnitaire[$dal->getNumeroLigne()])) {
                $dal->setPrixUnitaire($prixUnitaire[$dal->getNumeroLigne()]);
            }

            $this->em->persist($dal);
        }

        // 3. Mise à jour des lignes DALR
        /** @var iterable<DemandeApproLR> $dalrs les lignes de DALR correspondant au numéro de la DA $numDa */
        $dalrs = $this->em->getRepository(DemandeApproLR::class)->findBy(['numeroDemandeAppro' => $numDa]);
        foreach ($dalrs as $dalr) {
            $dalr
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(StatutDaConstant::STATUT_VALIDE);

            $this->mettreAJourChoixDalr($dalr, $refsValide);

            $this->em->persist($dalr);
        }

        return $da;
    }

    private function mettreAJourChoixDalr(DemandeApproLR $dalr, array $refsValide): void
    {
        if (empty($refsValide)) return;

        $dalr->setChoix(false);

        $numeroLigne = $dalr->getNumeroLigne();
        $numeroLigneTableau = $dalr->getNumLigneTableau();

        if (isset($refsValide[$numeroLigne]) && $numeroLigneTableau == $refsValide[$numeroLigne]) {
            $dalr->setChoix(true);
        }
    }

    /** Création du fichier Excel et PDF pour une DA avec DIT */
    public function exporterDaAvecDitEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf($numDa, $numeroVersion, function ($numDa) {
            $this->daSoumissionValidationService->creationPDFAvecDit($numDa);
        });
    }

    /** Création du fichier Excel et PDF pour une DA directe */
    public function exporterDaDirectEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf($numDa, $numeroVersion, function ($numDa) {
            $this->daSoumissionValidationService->creationPDFDirect($numDa);
        });
    }

    /**
     * @param callable $strategieEnregistrementPDF la stratégie d'enregistrement du PDF
     */
    private function exporterDaEnExcelEtPdf(string $numDa, int $numeroVersion, callable $strategieEnregistrementPDF): array
    {
        // 1. Récupération des lignes rectifiées de la DA
        $donnees = $this->daService->getLignesRectifiees($numDa, $numeroVersion);

        // 2. Création du fichier PDF
        $strategieEnregistrementPDF($numDa);

        // 3. Transformation des entités en tableau pour Excel
        $donneesExcel = $this->convertirEntitesPourExcel($donnees);

        // 4. Génération du fichier Excel
        $fileName = $numDa . '_' . (new DateTime())->format('Ymd_His') . '.xlsx';
        $filePath = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/$fileName";
        (new ExcelService())->createSpreadsheetEnregistrer($donneesExcel, $filePath);

        return [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'donnees'  => $donnees,
        ];
    }

    private function convertirEntitesPourExcel(array $entities): array
    {
        $tableau = [];
        $tableau[] = ['constructeur', 'reference', 'quantité', '', 'designation', 'PU'];

        foreach ($entities as $entity) {
            $tableau[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
                '',
                in_array($entity->getArtConstp(), ['ZDI', 'CAR']) || $entity->getArtRefp() === 'ST' ? $entity->getArtDesi() : '',
                in_array($entity->getArtConstp(), ['ZDI', 'CAR']) || $entity->getArtRefp() === 'ST' ? $entity->getPrixUnitaire() : '',
            ];
        }

        return $tableau;
    }

    /** Valide une DA réappro (lignes + DA) puis met à jour la table DaAfficher */
    public function validerDemandeReappro(DemandeAppro $demandeAppro): void
    {
        $this->modifierStatut($demandeAppro, StatutDaConstant::STATUT_VALIDE);
        $this->daAfficherService->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro(), true, StatutDaConstant::STATUT_DW_A_VALIDE);
    }

    /** Refuse une DA réappro puis met à jour la table DaAfficher */
    public function refuserDemandeReappro(DemandeAppro $demandeAppro): void
    {
        $this->modifierStatut($demandeAppro, StatutDaConstant::STATUT_REFUSE_APPRO);
        $this->daAfficherService->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
    }

    private function modifierStatut(DemandeAppro $demandeAppro, string $statut): void
    {
        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            if ($statut === StatutDaConstant::STATUT_VALIDE) {
                $demandeApproL->setEstValidee(true);
                $demandeApproL->setValidePar($this->userDataService->getUser()->getNomUtilisateur());
            }

            $this->em->persist($demandeApproL);
        }

        $demandeAppro->setStatutDal($statut);
        $this->em->persist($demandeAppro);
        $this->em->flush();
    }
}
