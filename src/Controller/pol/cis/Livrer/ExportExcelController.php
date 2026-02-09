<?php

namespace App\Controller\pol\cis\Livrer;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Service\ExcelService;

/**
 * @Route("/pol/cis")
 */
class ExportExcelController extends Controller
{
    use ALivrerTrait;


    /**
     * @Route("/export-excel-cis-a-livrer", name="pol_export_excel_cis_a_livrer")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('cis_a_Livrer_search_criteria', []);

        $entities = $this->recupData($criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° OR', 'Date OR', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté cde', 'Qté à liv', 'Qté liv'];
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
                $entity['quantiterlivrer']
            ];
        }

        (new ExcelService())->createSpreadsheet($data);
    }
}
