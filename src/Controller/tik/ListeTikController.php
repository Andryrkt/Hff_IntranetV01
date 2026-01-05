<?php

namespace App\Controller\tik;

use App\Entity\tik\TikSearch;
use InvalidArgumentException;
use App\Controller\Controller;
use App\Form\tik\TikSearchType;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/it")
 */
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

        $user = $this->getUser();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($user);
        $autorisation = [
            'autoriser'            => $autoriser,
            'autoriserIntervenant' => $this->autorisationIntervenant($user),
            'autoriserValidateur'  => $this->autorisationValidateur($user),
        ];
        //FIN AUTORISATION

        $agenceServiceIps = $this->agenceServiceIpsObjet();

        $this->initialisationFormRecherche($autorisation, $agenceServiceIps, $tikSearch, $user);

        //création et initialisation du formulaire de la recherche
        $form = $this->getFormFactory()->createBuilder(TikSearchType::class, $tikSearch, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tikSearch = $form->getData();
        }

        $criteria = [];
        // transformer l'objet tikSearch en tableau
        $criteria = $tikSearch->toArray();
        //recupères les données du criteria dans une session nommé tik_search_criteria
        $this->getSessionService()->set('tik_search_criteria', $criteria);

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 50;

        $option = [
            'autorisation' => $autorisation,
            'user'         => $user,
            'idAgence'     => $tikSearch->getAgenceEmetteur() === null ? null :  $tikSearch->getAgenceEmetteur()->getId(),
            'idService'    => $tikSearch->getServiceEmetteur() === null ? null : $tikSearch->getServiceEmetteur()->getId()
        ];

        $paginationData = $this->getEntityManager()->getRepository(DemandeSupportInformatique::class)->findPaginatedAndFiltered($page, $limit, $tikSearch, $option);

        $ticketsWithEditPermission = [];
        $ticketsWithCloturePermission = [];
        $ticketsWithReouverturePermission = [];
        foreach ($paginationData['data'] as $ticket) {
            $ticketsWithEditPermission[$ticket->getId()] = $this->canEdit($ticket->getNumeroTicket()); // Appel à la méthode canEdit
            $ticketsWithCloturePermission[$ticket->getId()] = $this->conditionCloturerTicket($user, $ticket); // Appel à la méthode conditionCloturerTicket
            $ticketsWithReouverturePermission[$ticket->getId()] = $this->conditionReouvrirTicket($ticket); // Appel à la méthode conditionReouvrirTicket
        }

        $this->logUserVisit('liste_tik_index'); // historisation du page visité par l'utilisateur

        return $this->render('tik/demandeSupportInformatique/list.html.twig', [
            'autorisation'    => $autorisation,
            'data'            => $paginationData['data'],
            'ticketsEdit'     => $ticketsWithEditPermission,
            'ticketsCloture'  => $ticketsWithCloturePermission,
            'ticketsReouvrir' => $ticketsWithReouverturePermission,
            'currentPage'     => $paginationData['currentPage'],
            'totalPages'      => $paginationData['lastPage'],
            'resultat'        => $paginationData['totalItems'],
            'form'            => $form->createView(),
            'criteria'        => $criteria,
        ]);
    }

    private function initialisationFormRecherche(array $autorisation, array $agenceServiceIps, TikSearch $tikSearch, User $user)
    {
        // Initialisation des critères depuis la session
        $criteria = $this->getSessionService()->get('tik_search_criteria', []);

        // Définition des valeurs par défaut en fonction des autorisations
        $agenceIpsEmetteur  = $autorisation['autoriser'] ? null : $agenceServiceIps['agenceIps'];
        $serviceIpsEmetteur = $autorisation['autoriser'] ? null : $agenceServiceIps['serviceIps'];

        $entities['nomIntervenant'] = ($autorisation['autoriserIntervenant'] && empty($criteria)) ? $user : null; // pour intervenant: filtre sur intervenant utilisateur connecté
        $entities['statut']         = ($autorisation['autoriserValidateur'] && empty($criteria)) ? $this->getEntityManager()->getRepository(StatutDemande::class)->find('79') : null; // pour validateur: filtre sur statut ouvert

        // Si des critères existent, les utiliser pour définir les entités associées
        if (!empty(array_filter($criteria))) {
            $repositories = [
                'nomIntervenant' => User::class,
                'statut'         => StatutDemande::class,
                'niveauUrgence'  => WorNiveauUrgence::class,
                'categorie'      => TkiCategorie::class,
                'sousCategorie'  => TkiSousCategorie::class,
                'autreCategorie' => TkiAutresCategorie::class,
            ];

            $entities = [];
            foreach ($repositories as $key => $entityClass) {
                $entities[$key] = isset($criteria[$key])
                    ? $this->getEntityManager()->getRepository($entityClass)->find($criteria[$key])
                    : null;
            }

            $tikSearch
                ->setNumeroTicket($criteria['numeroTicket'] ?? null)
                ->setDemandeur($criteria['demandeur'] ?? null)
                ->setNumParc($criteria['numParc'] ?? null)
                ->setDateDebut($criteria['dateDebut'] ?? null)
                ->setDateFin($criteria['dateFin'] ?? null)
                ->setStatut($entities['statut'])
                ->setNiveauUrgence($entities['niveauUrgence'])
                ->setCategorie($entities['categorie'])
                ->setSousCategorie($entities['sousCategorie'])
                ->setAutresCategories($entities['autreCategorie']);
        }

        // Définition des propriétés générales
        $tikSearch
            ->setAgenceEmetteur($agenceIpsEmetteur)
            ->setServiceEmetteur($serviceIpsEmetteur)
            ->setStatut($entities['statut'])
            ->setAutoriser($autorisation['autoriser'] ?? false)
            ->setNomIntervenant($entities['nomIntervenant'])
        ;
    }

    private function autorisationRole(User $user): bool
    {
        return $this->hasRole($user, 1) || $this->hasRole($user, 2) || $this->hasRole($user, 8);
    }

    private function autorisationIntervenant(User $user): bool
    {
        return $this->hasRole($user, 8);
    }

    private function autorisationValidateur(User $user): bool
    {
        return $this->hasRole($user, 2);
    }

    /**
     * Vérifie si un utilisateur possède un rôle spécifique.
     *
     * @param User $user L'utilisateur à vérifier.
     * @param int $roleId L'identifiant du rôle à rechercher.
     * @return bool Retourne true si l'utilisateur possède le rôle, sinon false.
     */
    private function hasRole(User $user, int $roleId): bool
    {
        $roleIds = $user->getRoleIds();

        // S'assurer que $roleIds est un tableau avant de continuer.
        if (!is_array($roleIds)) {
            throw new InvalidArgumentException('Les rôles retournés doivent être un tableau.');
        }

        return in_array($roleId, $roleIds);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut éditer le ticket
     */
    private function canEdit(string $numTik): array
    {
        $ticket = $this->getEntityManager()->getRepository(DemandeSupportInformatique::class)->findOneBy(['numeroTicket' => $numTik]);
        $result = [
            'monTicket' => 0,
            'ouvert'    => in_array($ticket->getIdStatutDemande()->getId(), [58, 65]) ? 1 : 0, // le statut du ticket est ouvert ou en attente
        ];

        $this->verifierSessionUtilisateur();

        $utilisateur    = $this->getUser();

        if (is_null($utilisateur)) {
            $this->SessionDestroy();
            $this->redirectToRoute("security_signin");
        }

        $allTik = $utilisateur->getSupportInfoUser();

        foreach ($allTik as $tik) {
            // si le numéro du ticket appartient à l'utilisateur connecté et 
            if ($numTik === $tik->getNumeroTicket()) {
                $result['monTicket'] = 1;
                break;
            }
        }

        return $result;
    }

    /** 
     * Méthode pour les conditions de cloture d'un ticket
     * 
     * @param User $utilisateur l'utilisateur connecté
     * @param DemandeSupportInformatique $ticket le ticket à cloturer
     * 
     * @return array
     */
    private function conditionCloturerTicket(User $utilisateur, DemandeSupportInformatique $ticket): array
    {
        $result = [];

        if (in_array("VALIDATEUR", $utilisateur->getRoleNames())) {
            $result['profil'] = 2;
        } else if ($ticket->getUserId()->getId() === $utilisateur->getId()) {
            $result['profil'] = 1;
        } else if (in_array("INTERVENANT", $utilisateur->getRoleNames())) {
            $result['profil'] = 0;
        } else {
            $result['profil'] = -1;
        }

        $result['statut'] = $ticket->getIdStatutDemande()->getId();

        return $result;
    }

    /** 
     * Méthode pour les conditions de réouverture d'un ticket
     * 
     * @param DemandeSupportInformatique $ticket le ticket à cloturer
     * 
     * @return array
     */
    private function conditionReouvrirTicket(DemandeSupportInformatique $ticket): array
    {
        $result = [];

        $userInfo = $this->getSessionService()->get('user_info');
        $id = $userInfo['id'] ?? null;

        $result['profil'] = ($ticket->getUserId()->getId() === $id) ? 1 : 0;
        $result['statut'] = $ticket->getIdStatutDemande()->getId();

        return $result;
    }
}
