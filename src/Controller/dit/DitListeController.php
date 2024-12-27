<?php

namespace App\Controller\dit;


use App\Entity\dit\DitSearch;
use App\Controller\Controller;
use App\Form\dit\DitSearchType;
use App\Form\dit\DocDansDwType;
use App\Model\dit\DitListModel;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\dit\DitListTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Service\docuware\CopyDocuwareService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\DossierInterventionAtelierModel;

class DitListeController extends Controller
{
    use DitListTrait;

    /**
     * @Route("/dit", name="dit_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        //recuperation agence et service autoriser
        $agenceIds = $user->getAgenceAutoriserIds();
        $serviceIds = $user->getServiceAutoriserIds();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);

        $autorisationRoleEnergie = $this->autorisationRoleEnergie(self::$em);
        //FIN AUTORISATION

        $ditListeModel = new DitListModel();
        $ditSearch = new DitSearch();
        $agenceServiceIps = $this->agenceServiceIpsObjet();

        $this->initialisationRechercheDit($ditSearch, self::$em, $agenceServiceIps, $autoriser);

        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(DitSearchType::class, $ditSearch, [
            'method' => 'GET',
            //'idAgenceEmetteur' => $agenceServiceIps['agenceIps']->getId(),
            'autorisationRoleEnergie' => $autorisationRoleEnergie
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numParc = $form->get('numParc')->getData() === null ? '' : $form->get('numParc')->getData();
            $numSerie = $form->get('numSerie')->getData() === null ? '' : $form->get('numSerie')->getData();
            if (!empty($numParc) || !empty($numSerie)) {
                $idMateriel = $this->ditModel->recuperationIdMateriel($numParc, $numSerie);
                if (!empty($idMateriel)) {
                    $this->ajoutDonnerRecherche($form, $ditSearch);
                    $ditSearch->setIdMateriel($idMateriel[0]['num_matricule']);
                }
            } else {
                $this->ajoutDonnerRecherche($form, $ditSearch);
                $ditSearch->setIdMateriel($form->get('idMateriel')->getData());
            }
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $ditSearch->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('dit_search_criteria', $criteria);


        $agenceServiceEmetteur = $this->agenceServiceEmetteur($agenceServiceIps, $autoriser);
        $option = $this->Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds);
        $this->sessionService->set('dit_search_option', $option);

        $paginationData = $this->data($request, $ditListeModel, $ditSearch, $option, self::$em);

        /**  Docs à intégrer dans DW * */
        $formDocDansDW = self::$validator->createBuilder(DocDansDwType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $this->dossierDit($request, $formDocDansDW);

        $criteriaTab = $criteria;

        $criteriaTab['typeDocument']    = $criteria['typeDocument'] ? $criteria['typeDocument']->getDescription() : $criteria['typeDocument'];
        $criteriaTab['niveauUrgence']   = $criteria['niveauUrgence'] ? $criteria['niveauUrgence']->getDescription() : $criteria['niveauUrgence'];
        $criteriaTab['statut']          = $criteria['statut'] ? $criteria['statut']->getDescription() : $criteria['statut'];
        $criteriaTab['dateDebut']       = $criteria['dateDebut'] ? $criteria['dateDebut']->format('d-m-Y') : $criteria['dateDebut'];
        $criteriaTab['dateFin']         = $criteria['dateFin'] ? $criteria['dateFin']->format('d-m-Y') : $criteria['dateFin'];
        $criteriaTab['agenceEmetteur']  = $criteria['agenceEmetteur'] ? $criteria['agenceEmetteur']->getLibelleAgence() : $criteria['agenceEmetteur'];
        $criteriaTab['serviceEmetteur'] = $criteria['serviceEmetteur'] ? $criteria['serviceEmetteur']->getLibelleService() : $criteria['serviceEmetteur'];
        $criteriaTab['agenceDebiteur']  = $criteria['agenceDebiteur'] ? $criteria['agenceDebiteur']->getLibelleAgence() : $criteria['agenceDebiteur'];
        $criteriaTab['serviceDebiteur'] = $criteria['serviceDebiteur'] ? $criteria['serviceDebiteur']->getLibelleService() : $criteria['serviceDebiteur'];
        $criteriaTab['categorie']       = $criteria['categorie'] ? $criteria['categorie']->getLibelleCategorieAteApp() : $criteria['categorie'];

        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = array_filter($criteriaTab);

        dump($ditSearch, $criteria, $filteredCriteria);

        // Déterminer le type de log
        $logType = empty($filteredCriteria) ? ['dit_index'] : ['dit_index_search', $filteredCriteria];

        // Appeler la méthode logUserVisit avec les arguments définis
        $this->logUserVisit(...$logType);


        self::$twig->display('dit/list.html.twig', [
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['lastPage'],
            'criteria' => $criteria,
            'resultat' => $paginationData['totalItems'],
            'statusCounts' => $paginationData['statusCounts'],
            'form' => $form->createView(),
            'criteria' => $criteria,
            'formDocDansDW' => $formDocDansDW->createView()
        ]);
    }

    /**
     * @Route("/export-excel", name="export_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('dit_search_criteria', []);
        //recupère les critères dans la session 
        $options = $this->sessionService->get('dit_search_option', []);

        //crée une objet à partir du tableau critère reçu par la session
        $ditSearch = $this->transformationEnObjet($criteria);

        $entities = $this->DonnerAAjouterExcel($ditSearch, $options, self::$em);

        // Convertir les entités en tableau de données
        $data = $this->transformationEnTableauAvecEntet($entities);
        //creation du fichier excel
        $this->excelService->createSpreadsheet($data);
    }


    /**
     * @Route("/cloturer-annuler/{id}", name="cloturer_annuler_dit_liste")
     */
    public function clotureStatut($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);
        $statutCloturerAnnuler = self::$em->getRepository(StatutDemande::class)->find(52);

        $this->changementStatutDit($dit, $statutCloturerAnnuler);

        $fileName = 'fichier_cloturer_annuler_' . $dit->getNumeroDemandeIntervention() . '.csv';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/Upload/dit/csv/' . $fileName;
        $headers = ['numéro DIT', 'statut'];
        $data = [
            $dit->getNumeroDemandeIntervention(),
            'Clôturé annulé'
        ];
        $this->ajouterDansCsv($filePath, $data, $headers);

        $copyDocuwareService = new CopyDocuwareService();
        $copyDocuwareService->copyCsvToDw($fileName, $filePath);

        $message = "La DIT a été clôturé avec succès.";
        $this->notification($message);
        $this->redirectToRoute("dit_index");
    }

    private function changementStatutDit($dit, $statutCloturerAnnuler)
    {
        $dit->setIdStatutDemande($statutCloturerAnnuler);
        self::$em->persist($dit);
        self::$em->flush();
    }

    private function ajouterDansCsv($filePath, $data,  $headers = null)
    {
        $fichierExiste = file_exists($filePath);

        // Ouvre le fichier en mode append
        $handle = fopen($filePath, 'a');

        // Si le fichier est nouveau, ajouter les en-têtes
        if (!$fichierExiste && $headers !== null) {
            fputcsv($handle, $headers);
        }

        // Ajoute les nouvelles données
        fputcsv($handle, $data);
        // fwrite($handle, "\n");

        // Ferme le fichier
        fclose($handle);
    }

    /**
     * @Route("/dw-intervention-atelier-avec-dit/{numDit}", name="dw_interv_ate_avec_dit")
     */
    public function dwintervAteAvecDit($numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dwModel = new DossierInterventionAtelierModel();

        // Récupérer les données de la demande d'intervention et de l'ordre de réparation
        $dwDit = $dwModel->findDwDit($numDit) ?? [];
        foreach ($dwDit as $key => $value) {
            $dwDit[$key]['nomDoc'] = 'Demande d\'intervention';
        }
        // dump($dwDit);
        $dwOr = $dwModel->findDwOr($numDit) ?? [];
        // dump($dwOr);
        $dwfac = [];
        $dwRi = [];
        $dwCde = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (!empty($dwOr)) {
            $dwfac = $dwModel->findDwFac($dwOr[0]['numero_doc']) ?? [];
            $dwRi = $dwModel->findDwRi($dwOr[0]['numero_doc']) ?? [];
            $dwCde = $dwModel->findDwCde($dwOr[0]['numero_doc']) ?? [];

            foreach ($dwOr as $key => $value) {
                $dwOr[$key]['nomDoc'] = 'Ordre de réparation';
            }

            foreach ($dwfac as $key => $value) {
                $dwfac[$key]['nomDoc'] = 'Facture';
            }

            foreach ($dwRi as $key => $value) {
                $dwRi[$key]['nomDoc'] = 'Rapport d\'intervention';
            }
            foreach ($dwCde as $key => $value) {
                $dwCde[$key]['nomDoc'] = 'Commande';
            }
        }

        // Fusionner toutes les données dans un tableau associatif
        $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde);

        $this->logUserVisit('dw_interv_ate_avec_dit', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        self::$twig->display('dw/dwIntervAteAvecDit.html.twig', [
            'data' => $data,
        ]);
    }
}
