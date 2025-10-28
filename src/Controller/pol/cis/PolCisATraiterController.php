<?php

namespace App\Controller\Pol\cis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\cis\CisATraiterModel;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;
use App\Model\dit\DitModel;

/**
 * @Route("/pol/cis-pol")
 */
class PolCisATraiterController extends Controller
{
    use AtraiterTrait;
    use AutorisationTrait;

    private $ditModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditModel = new DitModel();
    }

    /**
     * @Route("/cis-liste-a-traiter", name="pol_cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $cisATraiterModel = new CisATraiterModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $data = [];
        if ($form->isSubmitted() && $form->isValid()) {
            //initialisation des critères par défaut
            $criteria = [
                "agenceUser" => $agenceUser,
                'orValide' => true
            ];

            //recupération des critère de recherche dans le formulaire
            $criteria = $form->getData();

            //enregistrer les critère de recherche dans la session
            $this->getSessionService()->set('pol_cis_a_traiter_search_criteria', $criteria);

            // Récupération des données
            $data = $this->recupData($cisATraiterModel, $criteria);
        }

        $this->logUserVisit('cis_liste_a_traiter'); // historisation du page visité par l'utilisateur

        return $this->render('pol/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    /**
     * @Route("/export-excel-a-traiter-cis", name="pol_export_excel_a_traiter_cis")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisATraiterModel();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('pol_cis_a_traiter_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° Or', 'Date Or', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['numdit'],
                $entity['numcis'],
                $entity['datecis'],
                $entity['agenceservicetravaux'],
                $entity['numor'],
                $entity['dateor'],
                $entity['agenceservicedebiteur'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['qte_dem'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->getExcelService()->createSpreadsheet($data);
    }


    private function recupData($cisATraiterModel, $criteria)
    {
        $ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $numORItvValides = $this->orEnString($ditOrsSoumisRepository->findNumOrItvValide());

        $data = $cisATraiterModel->getListeCisATraiter($criteria, $numORItvValides);

        return $data;
    }
}
