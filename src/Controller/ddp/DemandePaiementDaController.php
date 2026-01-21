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
     * @Route("/newDa/{typeDdp}/{numCdeDa}/{typeDa}", name="demande_paiement_da", defaults={"numCdeDa"=null, "typeDa"=null})
     */
    public function index(int $typeDdp, int $numCdeDa, int $typeDa)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation acées */

        // creation du formulaire
        $dto = (new DemandePaiementFactory($this->getEntityManager()))->load($typeDdp, $numCdeDa, $typeDa);
        $form = $this->getFormFactory()->createBuilder(DemandePaiementDaType::class, $dto, [
            'method' => 'POST',
            'em' => $this->getEntityManager()
        ])->getForm();

        return $this->render('ddp/demande_paiement_da_new.html.twig', [
            'dto' => $dto,
            'form' => $form->createView()
        ]);
    }
}
