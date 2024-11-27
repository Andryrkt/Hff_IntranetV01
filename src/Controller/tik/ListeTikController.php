<?php

namespace App\Controller\tik;

use App\Entity\tik\TikSearch;
use App\Controller\Controller;
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
        $criteria =[];
        if($form->isSubmitted() && $form->isValid())
        {
            $criteria = $form->getData();
        }
        // transformer l'objet tikSearch en tableau
        $criteria = $criteria->toArray();
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

        $tikSearch
            ->setAgenceEmetteur($agenceIpsEmetteur)
            ->setServiceEmetteur($serviceIpsEmetteur)
            ->setAutoriser($autorisation['autoriser'])
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