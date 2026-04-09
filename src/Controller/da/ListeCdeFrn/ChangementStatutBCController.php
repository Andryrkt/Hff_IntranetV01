<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Constants\da\StatutBcConstant;
use App\Controller\Controller;
use App\Entity\da\DaAfficher;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class ChangementStatutBCController extends Controller
{

    private DaAfficherRepository $daAfficherRepository;



    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
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
            //modification dans la table da_afficher
            $numVersionMaxDaAfficher = $this->daAfficherRepository->getNumeroVersionMaxCde($numCde);
            /** @var DaAfficher[] $daAffichers */
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxDaAfficher]);
            $dateLivraison = new \DateTime($datePrevue);
            foreach ($daAffichers as $daAfficher) {
                $daAfficher
                    ->setStatutCde(StatutBcConstant::STATUT_BC_ENVOYE_AU_FOURNISSEUR)
                    ->setDateLivraisonPrevue($dateLivraison)
                    ->setJoursDispo($dateLivraison->diff(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->days)
                    ->setBcEnvoyerFournisseur(true)
                    ->setDateEnvoiFournisseur(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))
                ;
                $this->getEntityManager()->persist($daAfficher);
            }
            $this->getEntityManager()->flush();
            // envoyer une notification de succès
            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'statut modifié avec succès.']);
            $this->redirectToRoute("da_list_cde_frn");
        } else {
            $this->getSessionService()->set('notification', ['type' => 'error', 'message' => 'Erreur lors de la modification du statut... vous n\'avez pas cocher la cage à cocher.']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }
}
