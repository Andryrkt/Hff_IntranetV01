<?php

namespace App\Controller\Api;

use App\Constants\ddp\StatutConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\ddp\DemandePaiement;
use App\Model\ddp\DemandePaiementModel;
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
                    'message' => 'Aucun numéro BAP fourni.',
                ], 400);
            }


            $daSoumissionFacBlRepository = $this->getEntityManager()->getRepository(DaSoumissionFacBl::class)->getAllSelonNumBap($bapNumbers);
            $numeroCla = $this->genererNumeroCla();

            foreach ($daSoumissionFacBlRepository as $key => $value) {
                $ddp = new DemandePaiement();
                $numeroDdp = $this->genererNumeroDdp();
                $typeDdp = $this->getTypeDdp();

                if (null === $value->getNumeroFournisseur()) {
                    throw new \Exception("Le numéro de fournisseur est manquant pour le BAP : " . $value->getNumeroBap());
                }
                if (null === $value->getNumeroCde()) {
                    throw new \Exception("Le numéro de commande est manquant pour le BAP : " . $value->getNumeroBap());
                }

                // recup info ips pour la da
                $demandePaiementModel = new DemandePaiementModel();
                $infoIps = $demandePaiementModel->recupInfoPourDa($value->getNumeroFournisseur(), $value->getNumeroCde())[0] ?? [];

                if (empty($infoIps)) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'InfoIps est vide pour le BAP : ' . $value->getNumeroBap(),
                    ], 400);
                }


                // remplissage de la nouvelle demande de paiement
                $ddp->setNumeroDdp($numeroDdp)
                    ->setTypeDemandeId($typeDdp)
                    ->setNumeroFournisseur($infoIps['num_fournisseur'] ?? '')
                    ->setRibFournisseur($infoIps['rib'] ?? '')
                    ->setBeneficiaire($infoIps['nom_fournisseur'] ?? '')
                    ->setMotif("Bon a payer {$value->getNumeroFournisseur()} - {$value->getNumeroFactureFournisseur()}")
                    ->setAgenceDebiter($infoIps['code_agence'] ?? '')
                    ->setServiceDebiter($infoIps['code_service'] ?? '')
                    ->setStatut(StatutConstants::STATUT_SOUMIS_A_VALIDATION)
                    ->setAdresseMailDemandeur($this->getUserMail())
                    ->setDemandeur($this->getUserName())
                    ->setModePaiement($infoIps['mode_paiement'] ?? '')
                    ->setMontantAPayers($value->getMontantBlFacture())
                    ->setContact(Null)
                    ->setNumeroCommande([$infoIps['numero_cde']] ?? [])
                    ->setNumeroFacture([$value->getNumeroFactureFournisseur()] ?? [])
                    ->setStatutDossierRegul(Null)
                    ->setNumeroVersion(1)
                    ->setDevise($infoIps['devise'] ?? '')
                    ->setEstAutreDoc(false)
                    ->setNomAutreDoc(Null)
                    ->setEstCdeClientExterneDoc(false)
                    ->setNomCdeClientExterneDoc(Null)
                    ->setNumeroDossierDouane(Null)
                    ->setAppro(true)
                ;
                $this->getEntityManager()->persist($ddp);

                /** modification de la table da_soumission_fac_bl pour  le numéro de DDP créés, 
                 * le changement de statut BAP transmis à la compta 
                 * et la date de soumission compta */
                $value->setNumeroDemandePaiement($numeroDdp)
                    ->setStatutBap('Transmise')
                    ->setDateSoumissionCompta(new DateTime())
                    ->setNumeroCla($numeroCla)
                ;

                $this->getEntityManager()->persist($value);

                /** renomage et copie du fichier BAP dans DW */
                $fileCheckerService = new FileCheckerService($_ENV['BASE_PATH_FICHIER']);
                $bapFullpath = $fileCheckerService->getBapFullPath($value->getNumeroDemandeAppro(), $value->getNumeroCde());
                $fileNameForDW = $value->getNumeroBap() . '#' . $value->getNumeroCla() . '.pdf';
                $generatePdf = new GeneratePdf();
                $generatePdf->copyToDWBapDa($bapFullpath, $fileNameForDW);
            }

            $this->getEntityManager()->flush();


            return new JsonResponse([
                'success' => true,
                'message' => count($bapNumbers) . " demande(s) BAP ont été transmises avec succès. ($bapNumberString)",
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

    private function genererNumeroDdp(): string
    {
        //recupereation de l'application DDP pour generer le numero de ddp
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
        if (!$application) {
            throw new \Exception("L'application 'DDP' n'a pas été trouvée dans la configuration.");
        }
        //generation du numero de ddp
        $numeroDdp = AutoIncDecService::autoGenerateNumero('DDP', $application->getDerniereId(), true);
        //mise a jour de la derniere id de l'application DDP
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->getEntityManager(), $numeroDdp);

        return $numeroDdp;
    }

    private function getTypeDdp(): TypeDemande
    {
        // recupération du type de demande de paiement
        $typeApresLivraison = $this->getEntityManager()->getRepository(TypeDemande::class)->find(TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE);
        return  $typeApresLivraison;
    }
}
