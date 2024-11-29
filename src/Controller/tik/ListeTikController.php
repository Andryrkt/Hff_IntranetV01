<?php

namespace App\Controller\tik;

use App\Entity\tik\TikSearch;
use App\Controller\Controller;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Form\tik\TikSearchType;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;

class ListeTikController extends Controller
{
    /**
     * @Route("/tik-liste", name="liste_tik_index")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $tikSearch = new TikSearch();
        
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($user);
        $autoriserIntervenant = $this->autorisationIntervenant($user);
        $autorisation = [
            'autoriser' => $autoriser,
            'autoriserIntervenant' => $autoriserIntervenant
        ];
        //FIN AUTORISATION

        $agenceServiceIps= $this->agenceServiceIpsObjet();

        $this->initialisationFormRecherche( $autorisation, $agenceServiceIps, $tikSearch, $user);

        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(TikSearchType::class, $tikSearch, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $tikSearch = $form->getData();
        }

        $criteria=[];
        // transformer l'objet tikSearch en tableau
        $criteria = $tikSearch->toArray();
        //recupères les données du criteria dans une session nommé tik_search_criteria
        $this->sessionService->set('tik_search_criteria', $criteria);

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 10;

        $option = [
            'autorisation' => $autorisation,
            'user' => $user,
            'idAgence' => $tikSearch->getAgenceEmetteur() === null ? null :  $tikSearch->getAgenceEmetteur()->getId(),
            'idService' => $tikSearch->getServiceEmetteur() === null ? null : $tikSearch->getServiceEmetteur()->getId()
        ];

        $paginationData = self::$em->getRepository(DemandeSupportInformatique::class)->findPaginatedAndFiltered($page, $limit, $tikSearch, $option);
    
        self::$twig->display('tik/demandeSupportInformatique/list.html.twig', [
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages' =>$paginationData['lastPage'],
            'resultat' => $paginationData['totalItems'],
            'form' => $form->createView(),
            'criteria' => $criteria,
        ]);
    }

    private function initialisationFormRecherche(array $autorisation, array $agenceServiceIps, TikSearch $tikSearch, User $user)
    {
        $criteria = $this->sessionService->get('tik_search_criteria', []);
        
        if ($autorisation['autoriser']) {
            $agenceIpsEmetteur = null;
            $serviceIpsEmetteur = null;
        } else {
            $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
            $serviceIpsEmetteur = $agenceServiceIps['serviceIps'];
        }

        if($autorisation['autoriserIntervenant']) {
            $intervenant = $user;
        } else {
            $intervenant = null;
        }

        if (!empty(array_filter($criteria))) {
            $intervenant    = isset($criteria['nomIntervenant']) ? self::$em->getRepository(User::class)              ->find($criteria['nomIntervenant']) : null;
            $statut         = isset($criteria['statut'])         ? self::$em->getRepository(StatutDemande::class)     ->find($criteria['statut'])         : null;
            $niveauUrgence  = isset($criteria['niveauUrgence'])  ? self::$em->getRepository(WorNiveauUrgence::class)  ->find($criteria['niveauUrgence'])  : null;
            $categorie      = isset($criteria['categorie'])      ? self::$em->getRepository(TkiCategorie::class)      ->find($criteria['categorie'])      : null;
            $sousCategorie  = isset($criteria['sousCategorie'])  ? self::$em->getRepository(TkiSousCategorie::class)  ->find($criteria['sousCategorie'])  : null;
            $autreCategorie = isset($criteria['autreCategorie']) ? self::$em->getRepository(TkiAutresCategorie::class)->find($criteria['autreCategorie']) : null;

            $tikSearch
                ->setNumeroTicket($criteria['numeroTicket'] ?? null)
                ->setDemandeur($criteria['demandeur'] ?? null)
                ->setNumParc($criteria['numParc'] ?? null)
                ->setDateDebut($criteria['dateDebut'] ?? null)
                ->setDateFin($criteria['dateFin'] ?? null)
                ->setStatut($statut)
                ->setNiveauUrgence($niveauUrgence)
                ->setCategorie($categorie)
                ->setSousCategorie($sousCategorie)
                ->setAutresCategories($autreCategorie)
            ;
        }
        

        $tikSearch
            ->setAgenceEmetteur($agenceIpsEmetteur)
            ->setServiceEmetteur($serviceIpsEmetteur)
            ->setAutoriser($autorisation['autoriser'] ?? false)
            ->setNomIntervenant($intervenant)
        ;
    }

    private function autorisationRole($user): bool
    {
        /** CREATION D'AUTORISATION */
        $roleIds = $user->getRoleIds();
        return in_array(1, $roleIds) || in_array(2, $roleIds) || in_array(8, $roleIds);
    }

    private function autorisationIntervenant($user): bool
    {
        $roleIds = $user->getRoleIds();
        return in_array(8, $roleIds);
    }
}