<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class ChangementStatutBCController extends Controller
{

    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;


    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = self::$em->getRepository(DaAfficher::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
    }
    /**
     * @Route(path="/changement-statuts-envoyer-fournisseur/{numCde}/{datePrevue}/{estEnvoyer}", name="changement_statut_envoyer_fournisseur")
     *
     * @return void
     */
    public function changementStatutEnvoyerFournisseur(string $numCde = '', string $datePrevue = '', bool $estEnvoyer = false)
    {
        $this->verifierSessionUtilisateur();

        if ($estEnvoyer) {
            // modification de statut dans la soumission bc
            $numVersionMaxSoumissionBc = $this->daSoumissionBcRepository->getNumeroVersionMax($numCde);
            $soumissionBc = $this->daSoumissionBcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxSoumissionBc]);
            if ($soumissionBc) {
                $soumissionBc->setStatut(DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR);
                self::$em->persist($soumissionBc);
            }

            //modification dans la table da_afficher
            $numVersionMaxDaValider = $this->daAfficherRepository->getNumeroVersionMaxCde($numCde);
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxDaValider]);
            foreach ($daAffichers as $valider) {
                $valider->setStatutCde(DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR)
                    ->setDateLivraisonPrevue(new \DateTime($datePrevue))
                    ->setBcEnvoyerFournisseur(true)
                ;
                self::$em->persist($valider);
            }
            self::$em->flush();
            // envoyer une notification de succès
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'statut modifié avec succès.']);
            $this->redirectToRoute("da_list_cde_frn");
        } else {
            $this->sessionService->set('notification', ['type' => 'error', 'message' => 'Erreur lors de la modification du statut... vous n\'avez pas cocher la cage à cocher.']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }
}
