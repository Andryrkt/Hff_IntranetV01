<?php

namespace App\Controller\pol\cis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Model\magasin\cis\CisALivrerModel;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\magasin\cis\ALivrerSearchtype;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Model\dit\DitModel;

/**
 * @Route("/pol/cis")
 */
class PolCisALivrerController extends Controller
{
    use ALivrerTrait;
    use AutorisationTrait;

    private $ditModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditModel = new DitModel();
    }

    /**
     * @Route("/cis-liste-a-livrer", name="pol_cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $cisATraiterModel = new CisALivrerModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();



        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orValide" => true,
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($cisATraiterModel, $criteria);

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_cis_a_Livrer_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_livrer'); // historisation du page visité par l'utilisateur

        return $this->render('pol/cis/listALivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    /**
     * @Route("/export-excel-cis-a-livrer", name="pol_export_excel_cis_a_livrer")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisALivrerModel();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('pol_cis_a_Livrer_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° OR', 'Date OR', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté cde', 'Qté à liv', 'Qté liv', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['num_dit'],
                $entity['num_cis'],
                $entity['date_cis'],
                $entity['agence_service_travaux'],
                $entity['num_or'],
                $entity['date_or'],
                $entity['agence_service_debiteur_ou_client'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['quantitercommander'],
                $entity['quantiteralivrer'],
                $entity['quantiterlivrer'],
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
        $data = $cisATraiterModel->getlistOrALivrerPol($criteria, $numORItvValides);

        return $data;
    }
}
