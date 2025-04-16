<?php

namespace App\Controller\inventaire;

use DateTime;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use App\Model\inventaire\InventaireModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\inventaire\DetailInventaireSearch;
use App\Form\inventaire\detailInventaireSearchType;

class DetailInventaireController extends Controller
{
    use FormatageTrait;
    use Transformation;
    private InventaireModel $InventaireModel;
    private DetailInventaireSearch $DetailInventaireSearch;
    public function __construct()
    {
        parent::__construct();
        $this->InventaireModel = new InventaireModel;
        $this->DetailInventaireSearch = new DetailInventaireSearch;
    }
    /**
     * @Route("/inventaire_detail", name = "liste_detail_inventaire")
     * 
     * @return void
     */
    public function listeDetailInventaire(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(
            detailInventaireSearchType::class,
            $this->DetailInventaireSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();
        $form->handleRequest($request);
        $criteria = $this->DetailInventaireSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }
        $data = [];
        if ($request->query->get('action') !== 'oui') {
            $listInvent = $this->InventaireModel->ligneInventaire($criteria);
            $data = $this->recupData($listInvent);
        }
        self::$twig->display('inventaire/detailInventaire.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }
    /**
     * @Route("/export_excel_detail_inventaire", name = "export_excel_detail_inventaire")
     */
    public function exportExcel() {}
    public function recupData($inventDispo)
    {
        $data = [];
        if (!empty($inventDispo)) {
            for ($i = 0; $i < count($inventDispo); $i++) {
                $data[$i] = [
                    'numinv' => $inventDispo[$i]['numinv'],
                    'date' => $inventDispo[$i]['date'],
                    'nbr_comptage' => $inventDispo[$i]['nbr_comptage'],
                    'nb_bordereau' => $inventDispo[$i]['nb_bordereau'],
                    'ligne' => $inventDispo[$i]['ligne'],
                    'cst' => $inventDispo[$i]['cst'],
                    'ref' => $inventDispo[$i]['ref'],
                    'desi' => $inventDispo[$i]['desi'],
                    'casier' => $inventDispo[$i]['casier'],
                    'tsock' => $inventDispo[$i]['tsk'],
                    'prix' => $inventDispo[$i]['prix'],
                    'valeur_stock' => $inventDispo[$i]['valeur_stock'],
                    'comptage1' => $inventDispo[$i]['comptage1'],
                    'comptage2' => $inventDispo[$i]['comptage2'],
                    'comptage3' => $inventDispo[$i]['comptage3'],
                    'ecart' => $inventDispo[$i]['ecart'],
                    'montant_ecart' => $inventDispo[$i]['montant_ecart']
                ];
            }
        }
        return $data;
    }
}
