<?php

namespace App\Controller\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Controller\Traits\da\DemandeApproTrait;
use App\Entity\admin\Application;
use App\Form\dit\DitSearchType;
use App\Model\dit\DitListModel;
use App\Entity\admin\StatutDemande;
use App\Repository\dit\DitRepository;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Form\da\DemandeApproFormType;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\admin\dit\CategorieAteAppRepository;
use App\Repository\admin\dit\WorTypeDocumentRepository;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;

/**
 * @Route("/demande-appro")
 */
class DemandeApproController extends Controller
{
    use DemandeApproTrait;

    private DitSearch $ditSearch;
    private WorTypeDocumentRepository $worTypeDocumentRepository;
    private ServiceRepository $serviceRepository;
    private AgenceRepository $agenceRepository;
    private WorNiveauUrgenceRepository $worNiveauUrgenceRepository;
    private StatutDemandeRepository $statutDemandeRepository;
    private CategorieAteAppRepository $categorieAteAppRepository;
    private DitListModel $ditListModel;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->ditSearch = new DitSearch();
        $this->worTypeDocumentRepository = self::$em->getRepository(WorTypeDocument::class);
        $this->serviceRepository = self::$em->getRepository(Service::class);
        $this->agenceRepository = self::$em->getRepository(Agence::class);
        $this->worNiveauUrgenceRepository = self::$em->getRepository(WorNiveauUrgence::class);
        $this->statutDemandeRepository = self::$em->getRepository(StatutDemande::class);
        $this->categorieAteAppRepository = self::$em->getRepository(CategorieAteApp::class);
        $this->ditListModel = new DitListModel();
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/first-form", name="da_first_form")
     */
    public function firstForm()
    {
        self::$twig->display('da/first-form.html.twig');
    }

    /**
     * @Route("/list-dit", name="da_list_dit")
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
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();


        $criteria = [];
        $this->ajoutCriteredansSession($criteria);

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);
        $this->sessionService->set('dit_search_option', $option);

        //recupération des donnée
        $paginationData = $this->data($request, $option);


        self::$twig->display('da/list-dit.html.twig', [
            'data'          => $paginationData['data'],
            'currentPage'   => $paginationData['currentPage'],
            'totalPages'    => $paginationData['lastPage'],
            'criteria'      => $criteria,
            'resultat'      => $paginationData['totalItems'],
            'statusCounts'  => $paginationData['statusCounts'],
            'form'          => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="da_new")
     */
    public function new($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // obtenir le dit correspondant à l'id {id} du DIT
        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

        $demandeAppro = new DemandeAppro;
        $this->initialisationDemandeAppro($demandeAppro, $dit);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro
                ->setDemandeur($this->getUser()->getNomUtilisateur())
                ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ;

            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                $DAL
                    ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal('Ouvert')
                ;
                self::$em->persist($DAL);
            }

            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DAP']);
            $application->setDerniereId($demandeAppro->getNumeroDemandeAppro());

            self::$em->persist($application);
            self::$em->persist($demandeAppro);

            self::$em->flush();

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("da_list");
        }

        self::$twig->display('da/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list", name="da_list")
     */
    public function listeDA(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(DemandeAppro::class)->findAll();

        self::$twig->display(
            'da/list.html.twig',
            [
                'data' => $data
            ]
        );
    }
}
