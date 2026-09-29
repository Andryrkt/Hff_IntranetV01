<?php

namespace App\Api\ddp;


use App\Constants\ddp\StatutConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\admin\Application;
use App\Entity\da\DemandeAppro;
use App\Entity\ddp\DemandePaiement;
use App\Entity\dw\DwBcAppro;
use App\Model\da\DaModel;
use App\Model\dit\DitModel;
use App\Service\autres\AutoIncDecService;
use App\Service\da\FileCheckerService;
use App\Service\dataPdf\ordreReparation\Recapitulation;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\ddp\GeneratePdfDdpDa;
use App\Service\genererPdf\GeneratePdf;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DdpApiController extends Controller
{
    use PdfConversionTrait;

    /**
     * @Route("/api/transmettre-bap-compta", name="api_transmettre_bap_compta", methods={"POST"})
     */
    public function transmettreBap(Request $request)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $selectedDdp = $data['selectedDDP'] ?? [];
            $numerosBc = array_unique(array_column($selectedDdp, "numeroCde"));
            $ddpNumbers = array_column($selectedDdp, "numeroDdp");
            $ddpNumberString = implode(', ', $ddpNumbers);

            $result = $this->getValidationInfosWithStatus($selectedDdp, $numerosBc);

            if ($result['message']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }

            $validationInfo = $result['validationInfos'];

            $demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);

            /** @var DemandePaiement[] $ddps */
            $ddps = $demandePaiementRepository->findDdpByNumeroDdp($ddpNumbers);
            $numeroCla = $this->genererNumeroCla();

            foreach ($ddps as $ddp) {
                // modification du statut de demande de paiement
                $ddp
                    ->setStatut(StatutConstants::SOUMIS_A_VALIDATION)
                    ->setDateSoumissionCompta(new DateTime())
                    ->setNumeroCla($numeroCla)
                    ->setDeposerDw(true)
                    ->setDateDepotDw(new \DateTime())
                ;
                $this->getEntityManager()->persist($ddp);

                $nomCompletFichier = $this->genererPageDeGarde($validationInfo[$ddp->getNumeroCommande()] ?? [], $ddp);

                $this->fusionDesPdf($ddp->getNumeroCommande(), $nomCompletFichier);

                /** copie du fichier DDP dans DW */
                $fileCheckerService = new FileCheckerService();
                $bapFullpath = $fileCheckerService->getFullPath($ddp->getNumeroDdp());

                if (empty($bapFullpath)) {
                    throw new \Exception("Le fichier PDF pour la demande {$ddp->getNumeroDdp()} est introuvable sur le serveur.");
                }

                $fileNameForDW = $ddp->getNumeroDdp() . '#' . $numeroCla . '.pdf';
                $generatePdf = new GeneratePdf();

                $generatePdf->copyToDWBapDa($bapFullpath, $fileNameForDW, $ddp->getTypeDemandeId()->getCode());
            }

            $this->getEntityManager()->flush();

            return new JsonResponse([
                'success' => true,
                'message' => count($ddpNumbers) . " demande(s) DDP/BAP ont été transmises avec succès. ($ddpNumberString)",
            ]);
        } catch (\Throwable $e) {
            if (ob_get_length() > 0) {
                ob_clean();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la transmission des demandes BAP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function genererNumeroCla(): string
    {
        $em = $this->getEntityManager();
        //recupereation de l'application CLA pour generer le numero de cla
        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => 'CLA']);
        //generation du numero de cla
        $numeroCla = AutoIncDecService::autoGenerateNumero('CLA', $application->getDerniereId(), false);
        //mise a jour de la derniere id de l'application CLA
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $em, $numeroCla);

        return $numeroCla;
    }

    /**
     * @param array<int,array{numeroDdp:string,numeroCde:string}> $selectedDdp tableau contenant les demande de paiement selectionne
     * @param array<int,string> $numerosBc tableau contenant les numeros de bon de commande
     * 
     * @return array{message:?string,validationInfos:array<string,array{numeroBc:string,numeroOr:?string,validateur:?string,dateValidation:?string}>} tableau contenant le message de validation et les informations de validation
     */
    private function getValidationInfosWithStatus(array $selectedDdp, array $numerosBc): array
    {
        $dwBcApproRepository = $this->getEntityManager()->getRepository(DwBcAppro::class);
        $validationInfos = $dwBcApproRepository->findValidationInfosForBcs($numerosBc);

        $nonValides = array_keys(
            array_filter(
                $validationInfos,
                fn($info) => $info === null || empty($info['validateur']) // On filtre les BC qui ne sont pas validés (empty $info validateur) ou n'ont pas été soumis à validation ($info null)
            )
        );

        // Association BC => liste des DDP
        $ddpParBc = [];
        foreach ($selectedDdp as $ddp) {
            $ddpParBc[$ddp['numeroCde']][] = $ddp['numeroDdp'];
        }

        // Récupération des DDP concernées
        $ddpNonValides = [];
        foreach ($nonValides as $numeroBc) {
            $ddpNonValides = array_merge(
                $ddpNonValides,
                $ddpParBc[$numeroBc] ?? []
            );
        }

        $message = null;
        if (!empty($ddpNonValides)) {
            $message = sprintf(
                'Les DDP/BAP suivantes sont liées à un bon de commande non validé ou non soumis à validation : %s. Veuillez valider ou soumettre le(s) bon(s) de commande concerné(s).',
                implode(', ', array_unique($ddpNonValides))
            );
        }

        return [
            "message"         => $message,
            "validationInfos" => $validationInfos
        ];
    }

    /**
     * @param array{numeroBc:string,numeroOr:?string,validateur:?string,dateValidation:?string} $infoValidationBC tableau contenant les informations de validation
     * @param DemandePaiement $ddp demande de paiement
     * 
     * @return string
     */
    private function genererPageDeGarde(array $infoValidationBC, DemandePaiement $ddp): string
    {
        $numeroBc = $infoValidationBC['numeroBc'];

        if (empty($infoValidationBC)) throw new \Exception("Aucune information de validation trouvée pour le bon de commande $numeroBc.");

        $demandePaiementDto = $this->loadDemandePaiementDto($ddp, $infoValidationBC['numeroOr']);

        $numOr               = $demandePaiementDto->numeroOr;
        $codeSociete         = $demandePaiementDto->codeSociete;
        $numeroDdp           = $demandePaiementDto->numeroDdp;

        $historiqueLivraison = [];

        $infoMateriel        = (new DitModel)->recupInfoMateriel($numOr, $codeSociete);
        $dataRecapOR         = (new Recapitulation)->getData($numOr, $codeSociete);

        $demandeApproRepo    = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $demandeAppro        = $demandeApproRepo->findOneBy(['numeroDemandeAppro' => $demandePaiementDto->numeroDemandeAppro]);
        $infoFacBl           = [];

        $path = $_ENV['BASE_PATH_FICHIER'] . "/ddp/$numeroDdp";
        if (!is_dir($path)) mkdir($path, 0777, true);
        $nomAvecCheminFichier = "$path/$numeroDdp.pdf";

        $generatePdfDdp = new GeneratePdfDdpDa();
        $generatePdfDdp->generer($infoValidationBC, $infoMateriel, $dataRecapOR, $historiqueLivraison, $demandeAppro, $infoFacBl, $demandePaiementDto, $demandePaiementDto, $nomAvecCheminFichier);

        return $nomAvecCheminFichier;
    }

    private function loadDemandePaiementDto(DemandePaiement $ddp, ?string $numeroOr): DemandePaiementDto
    {
        $demandePaiementDto = new DemandePaiementDto();
        $demandePaiementDto->numeroDdp            = $ddp->getNumeroDdp();
        $demandePaiementDto->numeroOr             = $numeroOr;
        $demandePaiementDto->numeroCla            = $ddp->getNumeroCla();
        $demandePaiementDto->numeroDemandeAppro   = $ddp->getNumeroDemandeAppro();
        $demandePaiementDto->typeDemande          = $ddp->getTypeDemandeId();
        $demandePaiementDto->numeroFournisseur    = $ddp->getNumeroFournisseur();
        $demandePaiementDto->beneficiaire         = $ddp->getBeneficiaire();
        $demandePaiementDto->numeroCommande       = $ddp->getNumeroCommande();
        $demandePaiementDto->numeroFacture        = $ddp->getNumeroFacture();
        $demandePaiementDto->statut               = $ddp->getStatut();
        $demandePaiementDto->dateSoumissionCompta = $ddp->getDateSoumissionCompta();
        $demandePaiementDto->codeAgence           = $ddp->getAgenceDebiter();
        $demandePaiementDto->codeService          = $ddp->getServiceDebiter();
        $demandePaiementDto->dateDemande          = $ddp->getDateCreation();
        $demandePaiementDto->statutDossierRegul   = $ddp->getStatutDossierRegul();
        $demandePaiementDto->motif                = $ddp->getMotif();
        $demandePaiementDto->montantAPayer        = $ddp->getMontantAPayers();
        $demandePaiementDto->devise               = $ddp->getDevise();
        $demandePaiementDto->modePaiement         = $ddp->getModePaiement();
        $demandePaiementDto->demandeur            = $ddp->getDemandeur();
        $demandePaiementDto->adresseMailDemandeur = $ddp->getAdresseMailDemandeur();
        $demandePaiementDto->appro                = $ddp->getAppro() ?? false;
        $demandePaiementDto->ribFournisseur       = $ddp->getRibFournisseur();
        $demandePaiementDto->contact              = $ddp->getContact();
        $demandePaiementDto->codeSociete          = $ddp->getCodeSociete();
        $demandePaiementDto->infoBc               = (new DaModel)->getInfoBC($ddp->getNumeroCommande(), $ddp->getCodeSociete());

        return $demandePaiementDto;
    }

    private function fusionDesPdf(string $numeroCommande, string $nomAvecCheminFichier): void
    {
        $listeFichiersPJ = [];
        $path = rtrim($_ENV['BASE_PATH_FICHIER'], '/') . "/ddp/$numeroCommande";

        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^(_pj_|PJ_|devis_pj_)/', $file)) {
                    $listeFichiersPJ[] = $file;
                }
            }
        }

        $fichierConvertis = $this->ConvertirLesPdf($listeFichiersPJ);
        $traitementDeFichier = new TraitementDeFichier();
        $tousLesFichiersAvecChemin = $traitementDeFichier->insertFileAtPosition($fichierConvertis, $nomAvecCheminFichier, 0);
        $traitementDeFichier->fusionFichers($tousLesFichiersAvecChemin, $nomAvecCheminFichier);
    }
}
