<?php

namespace App\Controller\Api;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\da\DaSoumissionFacBl;
use App\Model\ddp\DemandePaiementModel;
use App\Service\autres\AutoIncDecService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DdpApiController extends Controller
{
    /**
     * @Route("/api/transmettre-bap-compta", name="api_transmettre_bap_compta", methods={"POST"})
     */
    public function transmettreBap(Request $request)
    {
        try {
            $this->verifierSessionUtilisateur();
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

            foreach ($daSoumissionFacBlRepository as $key => $value) {
                $ddp = new DemandePaiement();
                //recupereation de l'application DDP pour generer le numero de ddp
                $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
                if (!$application) {
                    throw new \Exception("L'application 'DDP' n'a pas été trouvée dans la configuration.");
                }
                //generation du numero de ddp
                $numeroDdp = AutoIncDecService::autoGenerateNumero('DDP', $application->getDerniereId(), true);
                //mise a jour de la derniere id de l'application DDP
                AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->getEntityManager(), $numeroDdp);
                // recupération du type de demande "DDP après livraison"
                $ddpApresLivraison = $this->getEntityManager()->getRepository(TypeDemande::class)->find(2);
                if (!$ddpApresLivraison) {
                    throw new \Exception("Le type de demande 'DDP après livraison' (ID 2) n'a pas été trouvé.");
                }
                // recupération des informations dans IPS
                $demandePaiementModel = new DemandePaiementModel();

                if (null === $value->getNumeroFournisseur()) {
                    throw new \Exception("Le numéro de fournisseur est manquant pour le BAP : " . $value->getNumeroBap());
                }
                if (null === $value->getNumeroCde()) {
                    throw new \Exception("Le numéro de commande est manquant pour le BAP : " . $value->getNumeroBap());
                }

                // recup info ips pour la da
                $infoIps = $demandePaiementModel->recupInfoPourDa($value->getNumeroFournisseur(), $value->getNumeroCde())[0] ?? [];

                if (empty($infoIps)) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'InfoIps est vide pour le BAP : ' . $value->getNumeroBap(),
                    ], 400);
                }


                // remplissage de la nouvelle demande de paiement
                $ddp->setNumeroDdp($numeroDdp)
                    ->setTypeDemandeId($ddpApresLivraison)
                    ->setNumeroFournisseur($infoIps['num_fournisseur'] ?? '')
                    ->setRibFournisseur($infoIps['rib'] ?? '')
                    ->setBeneficiaire($infoIps['nom_fournisseur'] ?? '')
                    ->setMotif("Bon a payer {$value->getNumeroFournisseur()} - {$value->getNumeroFactureFournisseur()}")
                    ->setAgenceDebiter($infoIps['code_agence'] ?? '')
                    ->setServiceDebiter($infoIps['code_service'] ?? '')
                    ->setStatut('Soumis à validation')
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
                ;
                $this->getEntityManager()->persist($value);
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
}
