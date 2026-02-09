<?php

namespace App\Controller\magasin\ors\Livrer;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\ors\MagasinOrALivrerTrait;
use App\Service\ExcelService;

/**
 * @Route("/magasin/or")
 */
class ExportExcelController extends Controller
{
    use MagasinOrALivrerTrait;

    /**
     * @Route("/magasin-list-or-livrer-export-excel", name="magasin_list_or_livrer")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('magasin_liste_or_livrer_search_criteria', []);

        $entities = $this->recupData($criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° Or', "Date planning", "Niv. d'urg", "Date Or", "Agence Emetteur", "Service Emetteur", 'Agence débiteur', 'Service débiteur', 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté demandée', 'Qté à livrer', 'Qté déjà livrée', 'Utilisateur', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {

            $data[] = [
                $entity['referencedit'],
                $entity['numeroor'],
                $entity['dateplanning'],
                $entity['niveauUrgence'] ?? '',
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
                $entity['nomprenom'],
                $entity['idmateriel'],
                $entity['marque'],
                $entity['casie']
            ];
        }

        (new ExcelService())->createSpreadsheet($data);
    }
}
