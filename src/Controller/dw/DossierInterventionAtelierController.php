<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Traits\FileUtilityTrait;
use App\Entity\admin\Application;
use App\Entity\da\DemandeApproLR;
use App\Entity\admin\utilisateur\Role;
use App\Controller\Traits\AutorisationTrait;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\dossierInterventionAtelierModel;
use App\Form\dw\DossierInterventionAtelierSearchType;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

/**
 * @Route("/atelier/demande-intervention")
 */
class DossierInterventionAtelierController extends Controller
{
    use FileUtilityTrait;
    use AutorisationTrait;

    private $historiqueOperation;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $this->getEntityManager()->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $this->getEntityManager()->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/dit-dossier-intervention-atelier", name="dit_dossier_intervention_atelier")
     *
     * @return void
     */
    public function dossierInterventionAtelier(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(DossierInterventionAtelierSearchType::class, null, ['method' => 'GET'])->getForm();

        $dwModel = new dossierInterventionAtelierModel();

        $dwDits = []; // Initialisation du tableau pour les demandes d'intervention
        $criteria = [
            "idMateriel"       => null,
            "typeIntervention" => "INTERNE",
            "dateDebut"        => null,
            "dateFin"          => null,
            "numParc"          => null,
            "numSerie"         => null,
            "numDit"           => null,
            "numOr"            => null,
            "designation"      => null,
        ];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $dwDits = $this->ajoutNbDoc($dwModel, $criteria);
        }

        $this->logUserVisit('dit_dossier_intervention_atelier'); // historisation du page visité par l'utilisateur

        return $this->render('dw/dossierInterventionAtelier.html.twig', [
            'form'   => $form->createView(),
            'dwDits' => $dwDits
        ]);
    }

    /**
     * @Route("/dw-intervention-atelier-avec-dit/{numDit}", name="dw_interv_ate_avec_dit")
     */
    public function dwintervAteAvecDit($numDit)
    {
        $dwModel = new dossierInterventionAtelierModel();

        // Récupération initiale : Demande d'intervention
        $dwDit = $this->fetchAndLabel($dwModel, 'findDwDit', $numDit, "Demande d'intervention");

        // Ordre de réparation et documents liés
        $dwOr = $this->fetchAndLabel($dwModel, 'findDwOr', $numDit, "Ordre de réparation");
        $dwFac = $dwRi = $dwCde = $dwBca = $dwFacBl = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (!empty($dwOr)) {
            $numeroDocOr = $dwOr[0]['numero_doc'];
            $dwFac   = $this->fetchAndLabel($dwModel, 'findDwFac',   $numeroDocOr, "Facture");
            $dwRi    = $this->fetchAndLabel($dwModel, 'findDwRi',    $numeroDocOr, "Rapport d'intervention");
            $dwCde   = $this->fetchAndLabel($dwModel, 'findDwCde',   $numeroDocOr, "Commande");
            $dwBca   = $this->fetchAndLabel($dwModel, 'findDwBca',   $numeroDocOr, "Bon de commande APPRO");
            $dwFacBl = $this->fetchAndLabel($dwModel, 'findDwFacBl', $numeroDocOr, "Facture / Bon de livraison");
        }

        // Documents liés à la demande d'intervention
        $dwBc  = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwBc',  $dwDit[0]['numero_doc'], "Bon de Commande Client") : [];
        $dwDev = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwDev', $dwDit[0]['numero_doc'], "Devis") : [];
        $daValide = !empty($dwDit) ? $this->getAllBaValide($numDit) : [];
        $daDevisPj = !empty($dwDit) ? $this->getAllDevisPjDA($numDit) : [];

        // Fusionner toutes les données
        $data = array_merge($dwDit, $dwOr, $dwFac, $dwRi, $dwCde, $dwBc, $dwDev, $dwBca, $dwFacBl, $daValide, $daDevisPj);

        $this->logUserVisit('dw_interv_ate_avec_dit', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        return $this->render('dw/dwIntervAteAvecDit.html.twig', [
            'numDit' => $numDit,
            'data'   => $data,
        ]);
    }

    public function ajoutNbDoc(dossierInterventionAtelierModel $dwModel, $criteria)
    {
        $dwDits = $dwModel->findAllDwDit($criteria, $this->getUser()->getCodeAgenceUser(), $this->hasRoles(Role::ROLE_ADMINISTRATEUR), $this->getUserName());

        $dwfac = $dwRi = $dwCde = $dwBc = $dwDev = $dwBca = $dwFacBl = [];

        for ($i = 0; $i < count($dwDits); $i++) {
            $numDit = $dwDits[$i]['numero_dit_intervention'];
            // Récupérer les données de la demande d'intervention et de l'ordre de réparation
            $dwDit = $dwModel->findDwDit($numDit) ?? [];
            $dwOr  = $dwModel->findDwOr($numDit) ?? [];

            // Si un ordre de réparation est trouvé, récupérer les autres données liées
            if (!empty($dwOr)) {
                $numeroDocOr = $dwOr[0]['numero_doc'];
                $dwfac   = $dwModel->findDwFac($numeroDocOr) ?? [];
                $dwRi    = $dwModel->findDwRi($numeroDocOr) ?? [];
                $dwCde   = $dwModel->findDwCde($numeroDocOr) ?? [];
                $dwBca   = $dwModel->findDwBca($numeroDocOr) ?? [];
                $dwFacBl = $dwModel->findDwFacBl($numeroDocOr) ?? [];
            }
            $dwBc  = $dwModel->findDwBc($dwDit[0]['numero_doc']) ?? [];
            $dwDev = $dwModel->findDwDev($dwDit[0]['numero_doc']) ?? [];
            $daValide = !empty($dwDit) ? $this->getAllBaValide($dwDit[0]['numero_doc']) : [];
            $daDevisPj = !empty($dwDit) ? $this->getAllDevisPjDA($dwDit[0]['numero_doc']) : [];

            // Fusionner toutes les données dans un tableau associatif
            $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde, $dwBc, $dwDev, $dwBca, $dwFacBl, $daValide, $daDevisPj);

            // Ajouter le nombre de documents à l'élément actuel de $dwDits
            $dwDits[$i]['nbDoc'] = count($data);
        }

        return $dwDits;
    }

    /**
     * Méthode utilitaire pour récupérer et étiqueter des documents
     */
    private function fetchAndLabel($model, string $method, $param, string $label): array
    {
        $items = $model->$method($param) ?? [];
        foreach ($items as &$item) {
            $item['nomDoc'] = $label;
        }
        return $items;
    }

    private function getAllBaValide(string $numeroDit)
    {
        $items = [];

        $allNumDaValide = $this->demandeApproRepository->findAllNumDaValide($numeroDit);

        foreach ($allNumDaValide as $numDaValide) {
            $chemin = "da/$numDaValide/$numDaValide.pdf";
            $items[] = [
                'nomDoc'         => "Bon d’achat validé",
                'numero_doc'     => $numDaValide,
                'taille_fichier' => $this->getFileSize($_ENV['BASE_PATH_FICHIER_COURT'] . "/$chemin"),
                'chemin'         => $chemin,
            ];
        }

        return $items;
    }

    private function getAllDevisPjDA(string $numeroDit)
    {
        $items = [];

        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDit($numeroDit);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDit($numeroDit);

        /** 
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);

        $allFileNames = [];
        foreach ($allRows as $row) {
            $files = $row['fileNames'];
            foreach ($files as $fileName) {
                $key = "{$row['numeroDemandeAppro']}_{$this->getFileNameWithoutExtension($fileName)}";
                if (!isset($allFileNames[$key])) {
                    $allFileNames[$key] = true;

                    $items[] = [
                        'nomDoc'            => "Devis / Pièce jointe",
                        'numero_doc'        => $key,
                        'chemin'            => "da/{$row['numeroDemandeAppro']}/$fileName",
                        'taille_fichier'    => $this->getFileSize($_ENV['BASE_PATH_FICHIER_COURT'] . "/da/{$row['numeroDemandeAppro']}/$fileName"),
                        'extension_fichier' => $this->getFileExtension($fileName),
                    ];
                }
            }
        }

        return $items;
    }
}
