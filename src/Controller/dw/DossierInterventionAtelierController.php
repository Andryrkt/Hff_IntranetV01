<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Form\dw\DossierInterventionAtelierSearchType;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

/**
 * @Route("/atelier")
 */
class DossierInterventionAtelierController extends Controller
{
    use AutorisationTrait;

    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService;
    }

    /**
     * @Route("/dit-dossier-intervention-atelier", name="dit_dossier_intervention_atelier")
     *
     * @return void
     */
    public function dossierInterventionAtelier(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DEMANDE_D_INTERVENTION);
        /** FIN AUtorisation acées */

        $form = self::$validator->createBuilder(DossierInterventionAtelierSearchType::class, null, ['method' => 'GET'])->getForm();

        $dwModel = new DossierInterventionAtelierModel();

        $dwDits = []; // Initialisation du tableau pour les demandes d'intervention
        $criteria = [
            "idMateriel" => null,
            "typeIntervention" => "INTERNE",
            "dateDebut" => null,
            "dateFin" => null,
            "numParc" => null,
            "numSerie" => null,
            "numDit" => null,
            "numOr" => null,
            "designation" => null,
        ];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $dwDits = $this->ajoutNbDoc($dwModel, $criteria);
        }

        $this->logUserVisit('dit_dossier_intervention_atelier'); // historisation du page visité par l'utilisateur

        self::$twig->display('dw/dossierInterventionAtelier.html.twig', [
            'form'   => $form->createView(),
            'dwDits' => $dwDits
        ]);
    }

    public function ajoutNbDoc(DossierInterventionAtelierModel $dwModel, $criteria)
    {
        $dwDits = $dwModel->findAllDwDit($criteria);

        $dwfac = $dwRi = $dwCde = $dwBc = $dwDev = $dwBca = $dwFacBl = [];

        for ($i = 0; $i < count($dwDits); $i++) {
            $numDit = $dwDits[$i]['numero_dit_intervention'];
            // Récupérer les données de la demande d'intervention et de l'ordre de réparation
            $dwDit = $dwModel->findDwDit($numDit) ?? [];
            $dwOr = $dwModel->findDwOr($numDit) ?? [];

            // Si un ordre de réparation est trouvé, récupérer les autres données liées
            if (!empty($dwOr)) {
                $numeroDocOr = $dwOr[0]['numero_doc'];
                $dwfac = $dwModel->findDwFac($numeroDocOr) ?? [];
                $dwRi = $dwModel->findDwRi($numeroDocOr) ?? [];
                $dwCde = $dwModel->findDwCde($numeroDocOr) ?? [];
                $dwBca = $dwModel->findDwBca($numeroDocOr) ?? [];
                $dwFacBl = $dwModel->findDwFacBl($numeroDocOr) ?? [];
            }
            $dwBc = $dwModel->findDwBc($dwDit[0]['numero_doc']) ?? [];
            $dwDev = $dwModel->findDwDev($dwDit[0]['numero_doc']) ?? [];

            // Fusionner toutes les données dans un tableau associatif
            $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde, $dwBc, $dwDev, $dwBca, $dwFacBl);

            // Ajouter le nombre de documents à l'élément actuel de $dwDits
            $dwDits[$i]['nbDoc'] = count($data);
        }

        return $dwDits;
    }
}
