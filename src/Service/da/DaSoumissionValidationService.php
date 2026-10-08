<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DaObservation;
use App\Entity\da\DaSoumisAValidation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Service\autres\VersionService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\da\GenererPdfDaAvecDit;
use App\Service\genererPdf\da\GenererPdfDaDirect;
use App\Service\genererPdf\da\GenererPdfDaReappro;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Génération des PDF de DA validée, soumission à validation (table DaSoumisAValidation) et dépôt dans DocuWare.
 */
class DaSoumissionValidationService
{
    private EntityManagerInterface $em;
    private DaService $daService;
    private GenererPdfDaAvecDit $genererPdfDaAvecDit;
    private GenererPdfDaDirect $genererPdfDaDirect;
    private GenererPdfDaReappro $genererPdfDaReappro;
    private TraitementDeFichier $traitementDeFichier;

    public function __construct(
        EntityManagerInterface $em,
        DaService $daService,
        GenererPdfDaAvecDit $genererPdfDaAvecDit,
        GenererPdfDaDirect $genererPdfDaDirect,
        GenererPdfDaReappro $genererPdfDaReappro,
        TraitementDeFichier $traitementDeFichier
    ) {
        $this->em = $em;
        $this->daService = $daService;
        $this->genererPdfDaAvecDit = $genererPdfDaAvecDit;
        $this->genererPdfDaDirect = $genererPdfDaDirect;
        $this->genererPdfDaReappro = $genererPdfDaReappro;
        $this->traitementDeFichier = $traitementDeFichier;
    }

    /** Création du PDF pour une DA avec DIT */
    public function creationPDFAvecDit(string $numDa): void
    {
        $da = $this->em->getRepository(DemandeAppro::class)->findAvecDernieresDALetLRParNumero($numDa);
        $dit = $da->getDit() ?? $this->em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);
        $this->genererPdfDaAvecDit->genererPdfBonAchatValide($dit, $da);
    }

    /** Création du PDF pour une DA directe */
    public function creationPDFDirect(string $numDa): void
    {
        $da = $this->em->getRepository(DemandeAppro::class)->findAvecDernieresDALetLRParNumero($numDa);
        $observations = $this->daService->getObservations($numDa);
        $this->genererPdfDaDirect->genererPdfBonAchatValide($da, $observations);
    }

    /** Création du PDF pour une DA réappro */
    public function creationPDFReappro(DemandeAppro $demandeAppro, iterable $observations, array $monthsList, array $dataHistoriqueConsommation): void
    {
        $this->genererPdfDaReappro->genererPdfBonAchatValide($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);
    }

    /** Dépôt dans DocuWare du PDF d'une DA réappro mensuel à valider */
    public function copyPDFToDW(string $numDa): void
    {
        $this->genererPdfDaReappro->copyToDWDaAValiderReapproMensuel($numDa, "");
    }

    /** Dépôt dans DocuWare du PDF d'une DA réappro ponctuel à valider */
    public function copyPDFToDWReapproPonctuel(string $numDa): void
    {
        $this->genererPdfDaReappro->copyToDWDaAValiderReapproPonctuel($numDa, "");
    }

    /** Ajoute la DA dans la table `DaSoumisAValidation` (version suivante, statut DW à valider) */
    public function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $repository = $this->em->getRepository(DaSoumisAValidation::class);
        $numeroVersionMax = $repository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());

        $daSoumisAValidation = (new DaSoumisAValidation())
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion(VersionService::autoIncrement($numeroVersionMax))
            ->setStatut(StatutDaConstant::STATUT_DW_A_VALIDE)
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->em->persist($daSoumisAValidation);
        $this->em->flush();
    }

    /** Fusionne le bon d'achat et ses pièces jointes (devis, observations) puis dépose le PDF dans DocuWare (DA directe) */
    public function fusionAndCopyToDW(string $numDa): void
    {
        $cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';

        $allDevisPj = $this->getDevisPjPathPDFDW($numDa);
        $bav = $cheminDeBase . "$numDa/$numDa.pdf";
        $fichiersConvertis = $this->convertirLesPdf($allDevisPj);
        array_unshift($fichiersConvertis, $bav);
        $nomAvecCheminPdfFusionner = $cheminDeBase . "$numDa/$numDa#_a_valider.pdf";
        $this->traitementDeFichier->fusionFichers($fichiersConvertis, $nomAvecCheminPdfFusionner);
        $this->genererPdfDaDirect->copyToDWDaAValiderDirect($numDa);
    }

    /** Chemins des devis et pièces jointes (lignes DAL, DALR et observations) */
    private function getDevisPjPathPDFDW(string $numDa): array
    {
        $pjDals = $this->em->getRepository(DemandeApproL::class)->findAttachmentsByNumeroDA($numDa);
        $pjDalrs = $this->em->getRepository(DemandeApproLR::class)->findAttachmentsByNumeroDA($numDa);
        $pjObservations = $this->em->getRepository(DaObservation::class)->findAttachmentsByNumeroDA($numDa);

        $filePaths = [];
        foreach (array_merge($pjDals, $pjDalrs, $pjObservations) as $row) {
            foreach ($row['fileNames'] ?? [] as $fileName) {
                $filePaths[] = "{$_ENV['BASE_PATH_FICHIER']}/da/$numDa/$fileName";
            }
        }
        return $filePaths;
    }

    private function convertirLesPdf(array $tousLesFichersAvecChemin): array
    {
        $tousLesFichiers = [];
        foreach ($tousLesFichersAvecChemin as $filePath) {
            $tousLesFichiers[] = $this->convertPdfWithGhostscript($filePath);
        }

        return $tousLesFichiers;
    }

    private function convertPdfWithGhostscript(string $filePath): string
    {
        $gsPath = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe'; // Modifier selon l'OS
        $tempFile = $filePath . "_temp.pdf";

        // Vérifier si le fichier existe et est accessible
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable : $filePath");
        }

        if (!is_readable($filePath)) {
            throw new Exception("Le fichier PDF ne peut pas être lu : $filePath");
        }

        // Commande Ghostscript
        $command = "\"$gsPath\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$tempFile\" \"$filePath\"";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $output);
            throw new Exception("Erreur lors de la conversion du PDF avec Ghostscript");
        }

        // Remplacement du fichier
        if (!rename($tempFile, $filePath)) {
            throw new Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $filePath;
    }
}
