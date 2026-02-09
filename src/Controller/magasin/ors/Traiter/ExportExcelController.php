<?php

namespace App\Controller\magasin\ors\Traiter;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Service\ExcelService;

/**
 * @Route("/magasin/or")
 */
class ExportExcelController extends Controller
{
    use MagasinOrATraiterTrait;

    /**
     * @Route("/magasin-list-or-traiter-export-excel", name="magasin_list_or_traiter")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('magasin_liste_or_traiter_search_criteria', []);

        //recupération des donnée dans le base de donnée
        $entities = $this->recupData($criteria);

        // Convertir les entités en tableau de données avec entête
        $data = $this->conversionEnTableauAvecEntete($entities);

        (new ExcelService())->createSpreadsheet($data);
    }

    public function conversionEnTableauAvecEntete(array $entities): array
    {
        $data = [];
        $data[] = ['N° DIT', 'N° Or', 'Date planning', "Niv. d'urg", "Date Or", "Agence Emetteur", "Service Emetteur", 'Agence Débiteur', 'Service Débiteur', 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté demandée', 'Utilisateur', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['referencedit'],
                $entity['numeroor'],
                $entity['dateplanning'],
                $entity['niveauUrgence'] ?? '',
                $entity['datecreation'],
                $entity['agencecrediteur'],
                $entity['servicecrediteur'],
                $entity['agence'],
                $entity['service'],
                $entity['numinterv'],
                $entity['numeroligne'],
                $entity['constructeur'],
                $entity['referencepiece'],
                $entity['designationi'],
                $entity['quantitedemander'],
                $entity['nomprenom'],
                $entity['idmateriel'],
                $entity['marque'],
                $entity['casie']
            ];
        }
        return $data;
    }
}
