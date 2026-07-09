<?php

namespace App\Api\ddp;


use App\Constants\ddp\StatutConstants;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\dw\DwBcAppro;
use App\Service\autres\AutoIncDecService;
use App\Service\da\FileCheckerService;
use App\Service\genererPdf\GeneratePdf;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DdpApiController extends Controller
{
    /**
     * @Route("/api/transmettre-bap-compta", name="api_transmettre_bap_compta", methods={"POST"})
     */
    public function transmettreBap(Request $request)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $selectedDdp = $data['selectedDDP'] ?? [];
            $ddpNumbers = array_column($selectedDdp, "numeroDdp");
            $ddpNumberString = implode(', ', $ddpNumbers);

            $result = $this->getValidationInfosWithStatus($selectedDdp);

            if ($result['message']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }

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
     * 
     * @return array{message:?string,validationInfos:array<string,array{numeroOr:?string,validateur:?string,dateValidation:?string}>} tableau contenant le message de validation et les informations de validation
     */
    private function getValidationInfosWithStatus(array $selectedDdp): array
    {
        $numerosBc = array_unique(array_column($selectedDdp, "numeroCde"));

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
}
