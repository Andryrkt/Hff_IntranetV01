<?php


namespace App\Controller\pol\ors;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Form\magasin\MagasinListeOrALivrerSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrALIvrerTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;
use App\Model\dit\DitModel;

/**
 * @Route("/pol/ors-pol")
 */
class PolListeOrLivrerController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrALIvrerTrait;

    private $ditModel;

    use AutorisationTrait;

    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditModel = new DitModel();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
    }

    /**
     * @Route("/liste-or-livrer", name="pol_or_liste_a_livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $codeAgence = $this->getUser()->getAgenceAutoriserCode();
        $serviceAgence = $this->getUser()->getServiceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orCompletNon" => "ORs COMPLET",
            "pieces" => "PIECES MAGASIN"
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_liste_or_livrer_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        return $this->render('pol/ors/listOrLivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }



    /**
     * @Route("/list-or-livrer-export-excel", name="pol_list_or_livrer")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('pol_liste_or_livrer_search_criteria', []);

        $entities = $this->recupData($criteria);


        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° Or', "Date planning", "Date Or", "Agence Emetteur", "Service Emetteur", 'Agence débiteur', 'Service débiteur', 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté demandée', 'Qté à livrer', 'Qté déjà livrée', 'Utilisateur', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {

            $data[] = [
                $entity['referencedit'],
                $entity['numeroor'],
                $entity['datePlanning'],
                $entity['datecreation'],
                $entity['agencecrediteur'],
                $entity['servicecrediteur'],
                $entity['agencedebiteur'],
                $entity['servicedebiteur'],
                $entity['numinterv'],
                $entity['numeroligne'],
                $entity['constructeur'],
                $entity['referencepiece'],
                $entity['designationi'],
                $entity['quantitedemander'],
                $entity['qtealivrer'],
                $entity['quantitelivree'],
                $entity['nomPrenom'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->getExcelService()->createSpreadsheet($data);
    }

    private function recupData($criteria)
    {
        /** @var string $numeroOrsItv @var string $numeroOr */
        [$numeroOrsItv, $numeroOr] = $this->recupNumOrSelonCondition($criteria);

        $data = $this->magasinListOrLivrerModel->getListeOrLivrerPol($criteria, $numeroOrsItv, $numeroOr);

        //ajouter le numero dit dans data
        for ($i = 0; $i < count($data); $i++) {
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $data[$i]['referencedit']]);
            if (!empty($ditRepository)) {
                $data[$i]['niveauUrgence'] = $ditRepository->getIdNiveauUrgence()->getDescription();
            } else {
                break;
            }
        }
        return $data;
    }
}
