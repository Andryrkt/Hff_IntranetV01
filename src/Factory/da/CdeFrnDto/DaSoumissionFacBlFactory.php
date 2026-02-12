<?php

namespace App\Factory\da\CdeFrnDto;

use DateTime;
use App\Model\da\DaModel;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DaSoumissionFacBl;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\autres\AutoIncDecService;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Model\da\DaSoumissionFacBlModel;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaFacBlService;

class DaSoumissionFacBlFactory
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private EntityManagerInterface $em;
    private DemandeApproRepository $demandeApproRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private HistoriqueOperationService $historiqueOperation;
    private DaModel $daModel;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->daSoumissionFacBlRepository = $em->getRepository(DaSoumissionFacBl::class);
        $this->historiqueOperation = new HistoriqueOperationDaFacBlService($em);
        $this->daModel = new DaModel();
    }

    public function initialisation(
        string $numCde,
        string $numDa,
        string $numOr,
        string $utilisateur
    ): DaSoumissionFacBlDto {
        $dto = new DaSoumissionFacBlDto();

        $dto->numeroCde = $numCde;
        $dto->utilisateur = $utilisateur;
        $dto->numeroVersion = $this->getNumeroVersion($numCde);
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroDemandeDit = $this->getNumeroDit($numDa);
        $dto->numeroOR = $numOr;
        $dto->dateBlFac = $this->getDateLivraisonPrevue($numDa, $numCde);

        // livraison ===========================
        $dto->infoLiv = $this->getInfoLivraison($numCde, $numDa);
        $dto->numLiv = array_keys($dto->infoLiv);

        // info commande (BC) ==========================
        $dto->infoBc = $this->getInfoBc($numCde);
        $dto->numeroFournisseur = $dto->infoBc['num_fournisseur'];
        $dto->nomFournisseur = $dto->infoBc['nom_fournisseur'];

        return $dto;
    }

    public function EnrichissementDtoApresSoumission(DaSoumissionFacBlDto $dto, $nomPdfFusionner = null)
    {
        if (empty($nomPdfFusionner)) return;

        $dto->pieceJoint1 = $nomPdfFusionner;
        $dto->montantBlFacture = (float)str_replace(',', '.', str_replace(' ', '', $dto->montantBlFacture ?? '0'));
        $dto->numeroFactureFournisseur = $this->getNumFacEtMontant($dto->numLiv)[0]['numero_facture'];

        // Bon à payer (BAP) ===============================
        $dto->numeroBap = $this->genererNumeroBap();
        $dto->statutBap = 'A transmettre';
        $dto->dateStatutBap = new DateTime();
        $dto->montantReceptionIps = $this->getNumFacEtMontant($dto->numLiv)[0]['montant_reception_ips'];

        // livraison ===========================
        $dto->dateClotLiv = new DateTime($dto->infoLiv[$dto->numLiv]['date_clot']);
        $dto->refBlFac = $dto->infoLiv[$dto->numLiv]['ref_fac_bl'];

        return $dto;
    }

    private function getNumeroDit(string $numDa): ?string
    {
        return $this->demandeApproRepository->getNumDitDa($numDa);
    }

    private function getDateLivraisonPrevue(string $numDa, string $numCde): ?DateTime
    {
        $dateLivraisonPrevue = $this->daAfficherRepository->getDateLivraisonPrevue($numDa, $numCde);
        return $dateLivraisonPrevue ? new DateTime($dateLivraisonPrevue) : null;
    }

    private function getInfoLivraison(string $numCde, string $numDa): array
    {
        $infosLivraisons = (new DaModel)->getInfoLivraison($numCde);

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a pas de livraison associé dans IPS. Merci de bien vérifier le numéro de la commande.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        $livraisonSoumis = $this->daSoumissionFacBlRepository->getAllLivraisonSoumis($numDa, $numCde);

        foreach ($livraisonSoumis as $numLiv) {
            unset($infosLivraisons[$numLiv]); // exclure les livraisons déjà soumises
        }

        if (empty($infosLivraisons)) {
            $message = "La commande n° <b>$numCde</b> n'a plus de livraison à soumettre. Toutes les livraisons associées ont déjà été soumises.";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');
        }

        return $infosLivraisons;
    }

    private function getInfoBc($numCde): array
    {
        return $this->daModel->getInfoBC($numCde);
    }

    private function EstDdpa($numCde): ?bool
    {
        $bcRepository = $this->em->getRepository(DaSoumissionBc::class);
        $numeroVersionMax = $bcRepository->getNumeroVersionMax($numCde);
        $bc =   $bcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numeroVersionMax]);
        return $bc->getDemandePaiementAvance();
    }

    private function genererNumeroBap(): string
    {
        //recupereation de l'application BAP pour generer le numero de bap
        $application = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => 'BAP']);
        //generation du numero de bap
        $numeroBap = AutoIncDecService::autoGenerateNumero('BAP', $application->getDerniereId(), true);
        //mise a jour de la derniere id de l'application BAP
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->em, $numeroBap);
        return $numeroBap;
    }

    private function getNumFacEtMontant($numLiv): array
    {
        $daSoumissionFacBlModel = new DaSoumissionFacBlModel();
        return $daSoumissionFacBlModel->getMontantReceptionIpsEtNumFac($numLiv);
    }

    private function getNumeroVersion($numCde): int
    {
        $numeroVersionMax = $this->daSoumissionFacBlRepository->getNumeroVersionMax($numCde);

        return AutoIncDecService::autoIncrement($numeroVersionMax);
    }
}
