<?php

namespace App\Controller\da\DemandeDevis;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\utilisateur\Role;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\demandeDevis\DaDemandeDevisTrait;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DemandeDevisController extends Controller
{
    use DaAfficherTrait;
    use AutorisationTrait;
    use DaDemandeDevisTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();

        $em = $this->getEntityManager();
        $this->daAfficherRepository     = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/demande-devis-en-cours/{id}", name="da_demande_devis_en_cours")
     */
    public function demandeDevisEnCours(int $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $demandeAppro = $this->demandeApproRepository->find($id);

        if (!$demandeAppro) {
            /** NOTIFICATION */
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'La demande d’achat que vous avez sélectionner n’existe pas.']);
            $this->redirectToRoute("list_da");
        }

        /** Autorisation accès */
        $this->checkPageAccess(($this->estUserDansServiceAppro() || $this->hasRoles(Role::ROLE_ADMINISTRATEUR)) && $demandeAppro->getStatutDal() === DemandeAppro::STATUT_SOUMIS_APPRO);
        /** FIN AUtorisation accès */

        $this->appliquerStatutDemandeDevisEnCours($demandeAppro, $this->getUserName());

        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro()); // enregistrer dans la table Da Afficher

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Le statut de la demande d’achat a été modifié avec succès.']);
        $this->redirectToRoute("list_da");
    }
}
