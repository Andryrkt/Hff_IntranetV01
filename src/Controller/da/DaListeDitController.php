<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Controller\Traits\da\DaListeDitTrait;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitSearch;
use App\Form\dit\DitSearchType;
use App\Repository\dit\DitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DaListeDitController extends Controller
{
    use DaListeDitTrait;
    private DitSearch $ditSearch;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ditSearch = new DitSearch();
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/demande-appro/list-dit", name="da_list_dit")
     * 
     * Methode pour afficher et faire une recherche sur la liste DIT
     */
    public function listeDIT(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupère les information de l'utilisateur connecter
        $user = $this->getUser();

        //recuperation agence et service autoriser
        $agenceIds = $user->getAgenceAutoriserIds();
        $serviceIds = $user->getServiceAutoriserIds();

        //creation d'autorisation
        $autoriser = $this->autorisationRole();
        $autorisationRoleEnergie = $this->autorisationRoleEnergie();

        //initialisation du champ de recherche
        $ditSearch = $this->initialisationRechercheDit();

        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            'interne_externe' => 'INTERNE',
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();

        $criteria = $this->recupDataFormulaireRecherhce($form, $request);

        //transformer l'objet ditSearch en tableau
        $criteriaTab = $criteria->toArray();

        $this->ajoutCriteredansSession($criteriaTab);

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);
        $this->sessionService->set('dit_search_option', $option);

        //recupération des donnée
        $paginationData = $this->data($request, $option, $criteria);


        self::$twig->display('da/list-dit.html.twig', [
            'data'          => $paginationData['data'],
            'currentPage'   => $paginationData['currentPage'],
            'totalPages'    => $paginationData['lastPage'],
            'criteria'      => $criteriaTab,
            'resultat'      => $paginationData['totalItems'],
            'statusCounts'  => $paginationData['statusCounts'],
            'form'          => $form->createView(),
        ]);
    }
}
