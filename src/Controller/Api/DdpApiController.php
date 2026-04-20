<?php

namespace App\Controller\Api;


use App\Constants\ddp\StatutConstants;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
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
            // $this->verifierSessionUtilisateur();
            $data = json_decode($request->getContent(), true);
            $bapNumbers = $data['bapNumbers'] ?? [];
            $bapNumberString = implode(', ', $bapNumbers);


            if (empty($bapNumbers)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Aucun numéro DDP/BAP fourni.',
                ], 400);
            }


            $demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
            $ddps = $demandePaiementRepository->findDdpByNumeroDdp($bapNumbers);
            $numeroCla = $this->genererNumeroCla();

            foreach ($ddps as $ddp) {
                // modification du statut de demande de paiement
                $ddp
                    ->setStatut(StatutConstants::STATUT_SOUMIS_A_VALIDATION)
                    ->setDateSoumissionCompta(new DateTime())
                    ->setNumeroCla($numeroCla);
                $this->getEntityManager()->persist($ddp);

                /** copie du fichier DDP dans DW */
                $fileCheckerService = new FileCheckerService($_ENV['BASE_PATH_FICHIER']);
                $bapFullpath = $fileCheckerService->getFullPath($ddp->getNumeroDdp());
                
                if (empty($bapFullpath)) {
                    throw new \Exception("Le fichier PDF pour la demande {$ddp->getNumeroDdp()} est introuvable sur le serveur.");
                }

                $fileNameForDW = $ddp->getNumeroDdp() . '#' . $numeroCla . '.pdf';
                $generatePdf = new GeneratePdf();
                $generatePdf->copyToDWBapDa($bapFullpath, $fileNameForDW);
            }

            $this->getEntityManager()->flush();


            return new JsonResponse([
                'success' => true,
                'message' => count($bapNumbers) . " demande(s) DDP/BAP ont été transmises avec succès. ($bapNumberString)",
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
}
