<?php

namespace App\Api\ddp;


use App\Constants\ddp\StatutConstants;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\dw\DwBcAppro;
use App\Service\autres\AutoIncDecService;
use App\Service\ddp\DemandePaiementFileService;
use App\Service\genererPdf\GeneratePdf;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
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
            $appro = (bool) ($data['appro'] ?? false);
            $selectedDdp = $data['selectedDDP'] ?? [];
            $numerosBc = array_unique(array_column($selectedDdp, "numeroCde"));
            $ddpNumbers = array_filter(array_column($selectedDdp, "numeroDdp"));
            $ddpNumberString = implode(', ', $ddpNumbers);

            // Avant genererNumeroCla : une transmission vide ne doit pas consommer de numéro
            if (empty($ddpNumbers)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Aucune demande DDP/BAP à transmettre n\'a été reçue.',
                ]);
            }
            // La vérification de validation des BC (DW) ne concerne que les DDP appro
            $result = $appro
                ? $this->getValidationInfosWithStatus($selectedDdp, $numerosBc)
                : ['message' => null, 'validationInfos' => []];

            if ($result['message']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }

            $demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);

            /** @var DemandePaiement[] $ddps */
            $ddps = $demandePaiementRepository->findDdpByNumeroDdp($ddpNumbers);

            // suivi étape par étape de la transmission (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DDP_TRANSMISSION);
            $numeroCla = $suivi->etape('Numéro CLA', fn() => $this->genererNumeroCla());
            $suivi->setNumeroDocument($numeroCla);

            foreach ($ddps as $ddp) {
                $suivi->etape('Transmission ' . $ddp->getNumeroDdp(), function () use ($ddp, $numeroCla) {
                    $this->transmettreDemande($ddp, $numeroCla);
                });
            }

            $suivi->etape('Base de données', function () {
                $this->getEntityManager()->flush();
            });

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

    private function transmettreDemande(DemandePaiement $ddp, string $numeroCla): void
    {
        // modification du statut de demande de paiement
        $ddp
            ->setStatut(StatutConstants::SOUMIS_A_VALIDATION)
            ->setDateSoumissionCompta(new DateTime())
            ->setNumeroCla($numeroCla)
            ->setDeposerDw(true)
            ->setDateDepotDw(new \DateTime())
        ;
        $this->getEntityManager()->persist($ddp);

        // copie telle quelle du PDF existant dans DW, appro comprise (pas de nouvelle page de garde ni de fusion) :
        // même résolution de chemin que la liste (chemin DW en BDD, sinon ddp/{numDdp}/{numDdp}.pdf)
        $fileInfo = (new DemandePaiementFileService())->getFileInfo($ddp->getNumeroDdp(), $ddp->getTypeDemandeId()->getCode());
        $bapFullpath = $fileInfo['exists'] ? $fileInfo['path'] : null;

        if (empty($bapFullpath)) {
            throw new \Exception("Le fichier PDF pour la demande {$ddp->getNumeroDdp()} est introuvable sur le serveur.");
        }

        $fileNameForDW = $ddp->getNumeroDdp() . '#' . $numeroCla . '.pdf';
        $generatePdf = new GeneratePdf();

        $generatePdf->copyToDWBapDa($bapFullpath, $fileNameForDW, $ddp->getTypeDemandeId()->getCode());
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
}
