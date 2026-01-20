<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Form\ddp\DemandePaiementDaType;
use App\Factory\ddp\DemandePaiementFactory;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DemandePaiementDaController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/newDa/{id}/{numCdeDa}", name="demande_paiement_da", defaults={"numCdeDa"=null})
     */
    public function index(int $id, int $numCdeDa)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation acées */

        // creation du formulaire
        $dto = (new DemandePaiementFactory($this->getEntityManager()))->load($id);
        $form = $this->getFormFactory()->createBuilder(DemandePaiementDaType::class, $dto)->getForm();

        return $this->render('ddp/demande_paiement_da_new.html.twig', [
            'dto' => $dto,
            'form' => $form->createView()
        ]);
    }
}
