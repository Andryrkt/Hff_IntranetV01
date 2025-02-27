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
        foreach ($allData as $data) {
            $dataExcel = array_merge($dataExcel, $model->recuperationDonneeConstructeur($data['referencepiece'], $data['constructeur']));
        }

        $this->excelService->createSpreadsheet($dataExcel);
    }
}
