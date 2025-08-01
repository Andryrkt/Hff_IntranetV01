<?php

namespace App\Controller\da\ListeDa;

use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Entity\admin\utilisateur\Role;
use App\Repository\admin\AgenceRepository;
use App\Repository\da\DaAfficherRepository;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DaHistoriqueDemandeModifDARepository;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DaHistoriqueDemandeModifDARepository $historiqueModifDARepository;
    private AgenceRepository $agenceRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = self::$em->getRepository(DaAfficher::class);
        $this->historiqueModifDARepository = self::$em->getRepository(DaHistoriqueDemandeModifDA::class);
        $this->agenceRepository = self::$em->getRepository(Agence::class);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        $numDaNonDeverrouillees = $this->historiqueModifDARepository->findNumDaOfNonDeverrouillees();

        //formulaire de recherche
        $form = self::$validator->createBuilder(DaSearchType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        // Donnée à envoyer à la vue
        $data = $this->getData($criteria);

        self::$twig->display('da/list_da.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'serviceAtelier'         => Controller::estUserDansServiceAtelier(),
            'serviceAppro'           => Controller::estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
    }

    public function getData(array $criteria): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $codeAgence = Controller::getUser()->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findOneBy(['codeAgence' => $codeAgence])->getId();

        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $this->daAfficherRepository->findDerniereVersionDesDA($criteria, $idAgenceUser);

        // Vérification du verrouillage des DA
        $daAffichers = $this->verouillerOuNonLesDa($daAffichers);

        // Retourne les DA filtrées
        return $daAffichers;
    }


    /** 
     * Vérifie si la DA doit être verrouillée ou non pour chaque DA filtrée
     * @param array $dasFiltered
     * @return array
     */
    private function verouillerOuNonLesDa($daAffichers)
    {
        foreach ($daAffichers as $daAfficher) {
            $this->estVerouillerOuNon($daAfficher);
        }
        return $daAffichers;
    }

    /** 
     * Vérifie si la DA doit être verrouillée ou non en fonction de son statut et du service de l'utilisateur
     */
    private function estVerouillerOuNon($daAfficher)
    {
        $statutDa = $daAfficher->getStatutDal(); // Récupération du statut de la DA
        $statutBc = $daAfficher->getStatutCde(); // Récupération du statut du BC

        $estAppro = Controller::estUserDansServiceAppro();
        $estAtelier = Controller::estUserDansServiceAtelier();
        $estAdmin = in_array(Role::ROLE_ADMINISTRATEUR, Controller::getUser()->getRoleIds());
        $verouiller = false; // initialisation de la variable de verrouillage à false (déverouillée par défaut)

        $statutDaVerouillerAppro = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE];
        $statutDaVerouillerAtelier = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_SOUMIS_APPRO];

        if (!$estAdmin && $estAppro && in_array($statutDa, $statutDaVerouillerAppro) && $statutBc !== DaSoumissionBc::STATUT_REFUSE) {
            /** 
             * Si l'utilisateur est Appro mais n'est pas Admin, et que le statut de la DA est TERMINER ou VALIDE,
             * et que le statut de la soumission BC n'est pas REFUSE, alors on verrouille la DA. 
             **/
            $verouiller = true;
        } elseif (!$estAdmin && $estAtelier && in_array($statutDa, $statutDaVerouillerAtelier)) {
            /** 
             * Si l'utilisateur est Atelier mais n'est pas Admin, et que le statut de la DA est TERMINER ou VALIDE ou SOUMIS A APPRO, 
             * alors on verrouille la DA.
             **/
            $verouiller = true;
        } elseif (!$estAtelier && !$estAppro && !$estAdmin) {
            /** 
             * Si l'utilisateur n'est ni Appro ni Atelier, et n'est pas Administrateur,
             * alors on verrouille la DA.
             */
            $verouiller = true;
        }

        // On applique le verrouillage ou non à l'entité Da Valider ou Proposer
        $daAfficher->setVerouille($verouiller);
    }

    private function quelqueModifictionDansDatabase(array $datas)
    {
        foreach ($datas as $data) {
            $this->modificationDateRestant($data);
        }
        self::$em->flush();
    }

    /** 
     * Permet de calculer le nombre de jours restants pour chaque DAL
     */
    private function modificationDateRestant(DaAfficher $data): void
    {
        $this->ajoutNbrJourRestant($data);
        self::$em->persist($data);
    }

    /**
     * Permet de modifier le statut du BC
     *
     * @return void
     */
    private function modificationStatutBC() {}


    private function modificationQte() 
    {}

    private function modificationInfosOR()
    {
        
    }
}
