<?php

namespace App\Controller\bordereau;

use DateTime;
use App\Controller\Controller;
use App\Model\bordereau\BordereauModel;
use App\Controller\Traits\FormatageTrait;
use App\Entity\Bordereau\BordereauSearch;
use App\Form\bordereau\BordereauSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\genererPdf\GeneretePdfBordereau;

/**
 * @Route("/bordereau")
 */
class bordereauController extends Controller
{

    use FormatageTrait;
    private BordereauModel $bordereauModel;
    private BordereauSearch $bordereauSearch;
    private GeneretePdfBordereau $generetePdfBordereau;
    public function __construct()
    {
        parent::__construct();
        $this->bordereauModel = new BordereauModel();
        $this->bordereauSearch = new BordereauSearch();
        $this->generetePdfBordereau = new GeneretePdfBordereau;
    }

    /**
     * @Route("/liste", name = "bordereau_liste")
     * 
     * @return void
     */
    public function bordereauListe(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(
            BordereauSearchType::class,
            $this->bordereauSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->bordereauSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
            // dump($criteria);
        }

        //transformer l'objet zn tableau
        $criteriaTab = $criteria->toArray();
        $this->getSessionService()->set('bordereau_search_criteria', $criteriaTab);
        $data = [];
        if ($request->query->get('action') !== 'oui') {
            $data = $this->recupData($criteria->getNuminv());
        }
        return $this->render('bordereau/bordereau.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }

    /**
     * @Route("/export_pdf_bordereau/{numinv}", name = "export_pdf_bordereau")
     */
    public function pdfExport()
    {
        // Vérification si l'utilisateur est connecté
        $criteriaTab =  $this->getSessionService()->get('bordereau_search_criteria');
        $data = $this->recupData($criteriaTab['numInv']);
        // dd($data);
        $this->generetePdfBordereau->genererPDF($data);
    }
    public function recupData($criteria)
    {
        $data = [];
        $listBordereau = $this->bordereauModel->bordereauListe($criteria);
        if (!empty($listBordereau)) {
            for ($i = 0; $i < count($listBordereau); $i++) {
                $data[] = [
                    'numinv' => $listBordereau[$i]['numinv'],
                    'numBordereau' => $listBordereau[$i]['numbordereau'],
                    'ligne' => $listBordereau[$i]['ligne'],
                    'casier' => $listBordereau[$i]['casier'],
                    'cst' => $listBordereau[$i]['cst'],
                    'refp' => $listBordereau[$i]['refp'],
                    'descrip' => $listBordereau[$i]['descrip'],
                    'qte_theo' => $listBordereau[$i]['qte_theo'],
                    'qte_alloue' => $listBordereau[$i]['qte_alloue'],
                    'dateinv' => (new DateTime($listBordereau[$i]['dateinv']))->format('d/m/Y')
                ];
            }
        }

        return $data;
    }
}
