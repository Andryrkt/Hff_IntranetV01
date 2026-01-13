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
                //generation du numero de ddp
                $numeroDdp = AutoIncDecService::autoGenerateNumero('DDP', $application->getDerniereId(), true);
                //mise a jour de la derniere id de l'application DDP
                AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->getEntityManager(), $numeroDdp);
                // recupération du type de demande "DDP après livraison"
                $ddpApresLivraison = $this->getEntityManager()->getRepository(TypeDemande::class)->find(2);
                // recupération des informations dans IPS
                $demandePaiementModel = new DemandePaiementModel();
                // recup info ips pour la da
                $infoIps = $demandePaiementModel->recupInfoPourDa($value->getNumeroFournisseur(), $value->getNumeroCde());
                // remplissage de la nouvelle demande de paiement
                $ddp->setNumeroDdp($numeroDdp)
                    ->setTypeDemandeId($ddpApresLivraison)
                    ->setNumeroFournisseur($infoIps['num_fournisseur'] ?? '')
                    ->setRibFournisseur($infoIps['rib'] ?? '')
                    ->setBeneficiaire($infoIps['nom_fournisseur'] ?? '')
                    ->setMotif("Bon a payer {$value->getNumeroFournisseur()} - <numero_facture_fournisseur>")
                    ->setAgenceDebiter($infoIps['code_agence'] ?? '')
                    ->setServiceDebiter($infoIps['code_service'] ?? '')
                    ->setStatut('Soumis à validation')
                    ->setAdresseMailDemandeur($this->getUserMail())
                    ->setDemandeur($this->getUserName())
                    ->setModePaiement($infoIps['mode_paiement'] ?? '')
                    ->setMontantAPayers(0.00)
                    ->setContact(Null)
                    ->setNumeroCommande('["' . $infoIps['numero_cde'] . '"]')
                    ->setNumeroFacture('[]')
                    ->setStatutDossierRegul(Null)
                    ->setNumeroVersion(1)
                    ->setDevise($infoIps['devise'] ?? '')
                    ->setEstAutreDoc(false)
                    ->setNomAutreDoc(Null)
                    ->setEstCdeClientExterneDoc(false)
                    ->setNomCdeClientExterneDoc(Null)
                    ->setNumeroDossierDouane(Null)
                ;
                $this->getEntityManager()->persist($ddp);
            }

            $this->getEntityManager()->flush();

            return new JsonResponse([
                'success' => true,
                'message' => " demande(s) BAP ont été transmises avec succès.",
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la transmission des demandes BAP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
