<?php

namespace App\Controller\export;

use App\Controller\Controller;
use App\Model\export\ExportExcelModel;
use Symfony\Component\Routing\Annotation\Route;

class ExportExcelController extends Controller
{
    /**
     * @Route("/Export/Excel/specifique", name="export_excel_specifique")
     */
    public function detailExcel()
    {
        $model = new ExportExcelModel;
        $allData = $model->recuperationConstructeur();
        $dataExcel[] = [
            "constructeur",
            "referencepiece",
            "natmouv",
            "qte",
            "prix",
            "datemouv",
            "ident",
            "natop",
            "nomtiers",
            "numfac",
            "dateop",
            "module",
        ];
        $i=0;
        foreach ($allData as $data) {
            $rows = $model->recuperationDonneeConstructeur($data['referencepiece'], $data['constructeur']);
            foreach ($rows as $row) {
                $dataExcel[] = [
                    'constructeur' => $row['constructeur'],
                    'referencepiece' => $row['referencepiece'],
                    'natmouv' => $row['natmouv'] ?? '',
                    'qte' => $row['qte'] ?? '',
                    'prix' => $row['prix'] ?? '',
                    'datemouv' => $row['datemouv'] ?? '',
                    'ident' => $row['ident'] ?? '',
                    'natop' => $row['natop'] ?? '',
                    'nomtiers' => $row['nomtiers'] ?? '',
                    'numfac' => $row['numfac'] ?? '',
                    'dateop' => $row['dateop'] ?? '',
                    'module' => $row['module'] ?? '',
                ];
            }
            $i++;
            if ($i===10) {
                break;
            }
        }
        dump($dataExcel); 

        $this->excelService->createSpreadsheet($dataExcel);
    }
}
