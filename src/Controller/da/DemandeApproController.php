<?php

namespace App\Controller\da;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Form\dit\DitSearchType;
use App\Model\dit\DitListModel;
use App\Entity\admin\StatutDemande;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
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
     * Methode pour recupérer tous les données à afficher
     *
     * @param Request $request
     * @param array $option
     * @return void
     */
    private function data(Request $request, array $option)
    { 
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        //recupération des données filtrée
        $paginationData = $this->ditRepository->findPaginatedAndFiltered($page, $limit, $this->ditSearch, $option);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data']);

        return $paginationData;
    }

    /**
     * Methode qui recupère le n° serie et n° parc de chaque dit et l'ajouter dans les données à afficher
     *
     * @param array $data
     * @return void
     */
    private function ajoutNumSerieNumParc(array $data)
    {
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                if (!empty($data[$i]->getIdMateriel())) {
                
                // Associez chaque entité à ses valeurs de num_serie et num_parc
                $numSerieParc = $this->ditModel->recupNumSerieParc($data[$i]->getIdMateriel());
                if (!empty($numSerieParc)) {
                    $numSerie = $numSerieParc[0]['num_serie'];
                    $numParc = $numSerieParc[0]['num_parc'];
                    $data[$i]->setNumSerie($numSerie);
                    $data[$i]->setNumParc($numParc);
                } else {
                    $data[$i]->setNumSerie('');
                    $data[$i]->setNumParc('');
                }
                }
            }
        }
    }

    private function Option(bool $autoriser, bool $autorisationRoleEnergie, array $agenceServiceEmetteur, array $agenceIds, array $serviceIds): array
    {
        return  [
            'boolean' => $autoriser,
            'autorisationRoleEnergie' => $autorisationRoleEnergie,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getId(),
            'agenceAutoriserIds' => $agenceIds,
            'serviceAutoriserIds' => $serviceIds
        ];
    }

    /**
     * Methode pour recupérer l'agence et service de l'utilisateur connecter
     *
     * @param array $agenceServiceIps
     * @param boolean $autoriser
     * @return array
     */
    private function agenceServiceEmetteur(array $agenceServiceIps, bool $autoriser): array
    {

        //initialisation agence et service
        if ($autoriser) {
            $agence = null;
            $service = null;
        } else {
            $agence = $agenceServiceIps['agenceIps'];
            $service = $agenceServiceIps['serviceIps'];
        }

        return [
            'agence' => $agence,
            'service' => $service
        ];
    }

    /**
     * Ajouter les information de la recherche dans la session
     *
     * @param array $criteria
     * @return void
     */
    private function ajoutCriteredansSession(array $criteria)
    {
        //transformer l'objet ditSearch en tableau
        $criteria = $this->ditSearch->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('dit_search_criteria', $criteria);
    }

    /**
     * Methode pour autorisation de l'admin
     *
     * @return boolean
     */
    private function autorisationRole(): bool
    {
        $userConnecter = $this->getUser();
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds);
    }

    /**
     * Methode pour autorise le role atelier
     *
     * @return boolean
     */
    private function autorisationRoleEnergie(): bool
    {
        $userConnecter = $this->getUser();
        $roleIds = $userConnecter->getRoleIds();
        return in_array(5, $roleIds);
    }

     /**
     * Methode pour l'initialisation des donners dans les champs de formulaire
     */
    private function initialisationRechercheDit(): DitSearch
    {

        $criteria = $this->sessionService->get('dit_search_criteria', []);
        if ($criteria !== null) {
            $agenceIpsEmetteur = null;
            $serviceIpsEmetteur = null;
            $typeDocument = $criteria['typeDocument'] === null ? null : $this->worTypeDocumentRepository->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $this->worNiveauUrgenceRepository->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $this->statutDemandeRepository->find($criteria['statut']->getId());
            $serviceEmetteur = $criteria['serviceEmetteur'] === null ? $serviceIpsEmetteur : $this->serviceRepository->find($criteria['serviceEmetteur']->getId());
            $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $this->serviceRepository->find($criteria['serviceDebiteur']->getId());
            $agenceEmetteur = $criteria['agenceEmetteur'] === null ? $agenceIpsEmetteur : $this->agenceRepository->find($criteria['agenceEmetteur']->getId());
            $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $this->agenceRepository->find($criteria['agenceDebiteur']->getId());
            $categorie = $criteria['categorie'] === null ? null : $this->categorieAteAppRepository->find($criteria['categorie']);
        } else {
            $agenceIpsEmetteur = null;
            $serviceIpsEmetteur = null;
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $agenceEmetteur = $agenceIpsEmetteur;
            $serviceEmetteur = $serviceIpsEmetteur;
            $serviceDebiteur = null;
            $agenceDebiteur = null;
            $categorie = null;
        }

        $this->ditSearch
            ->setStatut($statut)
            ->setNiveauUrgence($niveauUrgence)
            ->setTypeDocument($typeDocument)
            ->setInternetExterne($criteria['interneExterne'] ?? null)
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setIdMateriel($criteria['idMateriel'] ?? null)
            ->setNumParc($criteria['numParc'] ?? null)
            ->setNumSerie($criteria['numSerie'] ?? null)
            ->setAgenceEmetteur($agenceEmetteur)
            ->setServiceEmetteur($serviceEmetteur)
            ->setAgenceDebiteur($agenceDebiteur)
            ->setServiceDebiteur($serviceDebiteur)
            ->setNumDit($criteria['numDit'] ?? null)
            ->setNumOr($criteria['numOr'] ?? null)
            ->setStatutOr($criteria['statutOr'] ?? null)
            ->setDitSansOr($criteria['ditSansOr'] ?? null)
            ->setCategorie($categorie)
            ->setUtilisateur($criteria['utilisateur'] ?? null)
            ->setSectionAffectee($criteria['sectionAffectee'] ?? null)
            ->setSectionSupport1($criteria['sectionSupport1'] ?? null)
            ->setSectionSupport2($criteria['sectionSupport2'] ?? null)
            ->setSectionSupport3($criteria['sectionSupport3'] ?? null)
            ->setEtatFacture($criteria['etatFacture'] ?? null)
        ;

        return $this->ditSearch;
    }

    /**
     * @Route("/new/{id}", name="da_new")
     */
    public function new($id)
    {
        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

        // $form = ;

        self::$twig->display('da/first-form.html.twig', [
            'dit'  => $dit,
            // 'form' => $form,
        ]);
    }
}
