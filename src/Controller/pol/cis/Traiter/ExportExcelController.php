<?php

namespace App\Controller\pol\cis\Traiter;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

/**
 * @Route("/pol/cis")
 */
class ExportExcelController extends Controller
{
    use AtraiterTrait;

    /**
     * @Route("/export-excel-a-traiter-cis", name="pol_export_excel_a_traiter_cis")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('cis_a_traiter_search_criteria', []);

        // recupération des données
        $entities = $this->recupData($criteria);

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
                $entity['idmateriel'],
                $entity['marque'],
                $entity['casie']
            ];
        }

        $this->getExcelService()->createSpreadsheet($data);
    }
}
