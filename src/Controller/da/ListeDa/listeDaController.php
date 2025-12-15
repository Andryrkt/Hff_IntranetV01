<?php

namespace App\Controller\da\ListeDa;

use App\Entity\da\DaSearch;
use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\Role;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaListeTrait();
        $this->initStatutBcTrait();
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        /** Initialisation DaSearch */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence           = $agenceServiceIps['agenceIps'];
        $codeCentrale     = $this->hasRoles(Role::ROLE_ADMINISTRATEUR) || in_array($agence->getCodeAgence(), ['90', '91', '92']);

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, ['method' => 'GET'])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSearch $daSearch */
            $daSearch = $form->getData();
        }
        $criteria = [];
        //transformer l'objet daSearch en tableau
        $criteria = $daSearch->toArray();
        //recupères les données du criteria dans une session nommé criteria_search_list_da
        $this->getSessionService()->set('criteria_search_list_da', $criteria);

        $sortJoursClass = false;

        if ($criteria && $criteria['sortNbJours']) $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        // Donnée à envoyer à la vue
        $paginationData = $this->getPaginationData($criteria, $page, $limit);
        $dataPrepared = $this->prepareDataForDisplay($paginationData['data']);

        /** === Formulaire pour la date de livraison prevu === */
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);

        return $this->render('da/list-da.html.twig', [
            'data'           => $dataPrepared,
            'form'           => $form->createView(),
            'criteria'       => $criteria,
            'codeCentrale'   => $codeCentrale,
            'daTypeIcons'    => $this->getAllIcons(),
            'sortJoursClass' => $sortJoursClass,
            'currentPage'    => $paginationData['currentPage'],
            'totalPages'     => $paginationData['lastPage'],
            'resultat'       => $paginationData['totalItems'],
            'formDateLivraison' => $formDateLivraison->createView()
        ]);
    }

    private function TraitementFormulaireDateLivraison(Request $request, FormInterface $formDateLivraison)
    {
        $formDateLivraison->handleRequest($request);

        if ($formDateLivraison->isSubmitted() && $formDateLivraison->isValid()) {
            //recupération des valeurs dans le formulaire
            $data = $formDateLivraison->getData();

            // recupération des lignes de commande dans le da_afficher
            $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroCde' => $data['numeroCde']]);

            //modification de la date livraison prevue sur chaque ligne
            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($data['dateLivraisonPrevue']);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Date de livraison prévue modifier avec succèss']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }
}
