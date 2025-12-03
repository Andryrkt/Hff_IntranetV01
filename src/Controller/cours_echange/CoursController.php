<?php

namespace App\Controller\cours_echange;

use App\Controller\Controller;
use App\Entity\cours_echange\CoursEchangeSearch;
use App\Form\cours_echange\CoursSearchType;
use App\Model\cours_echange\coursModel;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;

class CoursController extends Controller
{
    private coursModel $coursModel;
    private CoursEchangeSearch $coursEchangeSearch;
    public function __construct()
    {
        parent::__construct();
        $this->coursModel = new coursModel();
        $this->coursEchangeSearch = new CoursEchangeSearch();
    }

    /**
     * @Route("cours_echange", name="cours_echange")
     */
    public function viewCours()
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $data = [];
        $dateCours = $this->coursModel->recupDatenow();
        $coursDate = date('m/d/Y', strtotime($dateCours[0]));
        $data = $this->dataCours($coursDate)['data'];
        $montEU_USD = $this->dataCours($coursDate)['EU_USD'];
        $montUSD_EU = $this->dataCours($coursDate)['USD_EU'];
        return $this->render('cours_echange/cours_view.html.twig', [
            'libDate' => date('d/m/Y', strtotime($dateCours[0])),
            'deviscours' => $data,
            'EU_USD' => $montEU_USD,
            'USD_EU' => $montUSD_EU,
        ]);
    }

    public function dataCours($coursDate)
    {
        $data = [];
        $devis = $this->coursModel->recupDevis();
        for ($i = 0; $i < count($devis); $i++) {
            $dev = substr($devis[$i], 0, 2);
            $montCours = $this->coursModel->recupCours($coursDate, $dev);
            if ($dev = "EU") {
                $montEU = $this->coursModel->recupCours($coursDate, $dev);
            }
            if ($dev = "US") {
                $montUSD = $this->coursModel->recupCours($coursDate, $dev);
            }
            $montEU_USD = $montEU / $montUSD;
            $montUSD_EU = $montUSD / $montEU;

            $data[] = [
                'devis' => $devis[$i],
                'cours' => number_format($montCours, 2, ',', ' ')
            ];
        }
        return ['data' => $data, 'EU_USD' => $montEU_USD, 'USD_EU' => $montUSD_EU];
    }
    /**
     * @Route("historique_echange", name="historique_echange")
     */
    public function histoEchange(){
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $form = $this->getFormFactory()->createBuilder(
            CoursSearchType::class,
            $this->coursEchangeSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();
$criteria = $this->coursEchangeSearch;

          return $this->render('cours_echange/historique_cours.html.twig',[
            'form'=> $form->createView()
          ]);
    }
}
