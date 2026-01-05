<?php

namespace App\Controller\contrat;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Role;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/documentation")
 */
class ContratController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/nouveau-contrat", name="new_contrat")
     */
    public function nouveauContrat()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->hasRoles(Role::ROLE_ADMINISTRATEUR));

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => "https://hffc.docuware.cloud/docuware/formsweb/enregistrement-contrats?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Nouveau contrat",
            'bgColor'   => "bg-bleu-hff",
            'height'    => 980,
        ]);
    }
}
