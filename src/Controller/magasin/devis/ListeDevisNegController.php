<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use App\Form\magasin\devis\DevisNegSearchType;
use App\Mapper\Magasin\Devis\DevisNegMapper;
use App\Model\magasin\devis\DevisNegModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Routing\Annotation\Route;


class ListeDevisNegController extends Controller
{
    use AutorisationTrait;

    private DevisNegModel $listeDevisNegModel;
    private DevisNegMapper $devisNegMapper;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisNegModel = new DevisNegModel();
        $this->devisNegMapper = new DevisNegMapper();
    }

    /**
     * @Route("/liste-devis-neg", name="liste_devis_neg")
     *
     * @return void
     */
    public function listeDevisNeg()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        // création du formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisNegSearchType::class, null, [
            'em' => $this->getEntityManager(),
            'method' => 'GET'
        ])->getForm();

        /** Récupération des devis et le transform en DTO */
        $devisNeg = $this->getDataDevisNegEnDto();

        return $this->render('magasin/devis/liste_devis_neg.html.twig', [
            'devisNeg' => $devisNeg,
            'form' => $form->createView(),
        ]);
    }

    public function getDataDevisNegEnDto()
    {
        $criteria = [];
        $codeAgenceAutoriserString = TableauEnStringService::orEnString($this->getUser()->getAgenceAutoriserCode());
        $vignette = 'magasin';
        $adminMutli = in_array(1, $this->getUser()->getRoleIds()) || in_array(6, $this->getUser()->getRoleIds());
        $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $this->listeDevisNegModel->getNumeroDevisExclure()));
        $devisNeg = $this->listeDevisNegModel->getDevisNeg($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure);
        $devisNeg = $this->devisNegMapper->map($devisNeg);
        return $devisNeg;
    }
}
