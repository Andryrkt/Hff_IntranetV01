<?php
namespace App\Controller\cours_echange;

use App\Controller\Controller;
use App\Model\cours_echange\coursModel;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
class CoursController extends Controller{
private coursModel $coursModel; 
    public function __construct()
    {
        parent::__construct();
        $this->coursModel = new coursModel();
    }

    /**
     * @Route("cours_echange", name="cours")
     */
    public function viewCours(){
        
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $data = [];
        $dateCours = $this->coursModel->recupDatenow();
        $coursDate = date('m/d/Y', strtotime($dateCours[0]));
        $data = $this->dataCours($coursDate);
        return $this->render('cours_echange/cours_view.html.twig',[
            'libDate' =>date('d/m/Y', strtotime($dateCours[0])),
            'deviscours'=> $data
        ]);
    }

    public function dataCours($coursDate){
        $data = [];
        $devis = $this->coursModel->recupDevis();
        for ($i=0; $i < count($devis) ; $i++) { 
           $montCours = $this->coursModel->recupCours($coursDate,$devis[$i]);
           $data[] = [
            'devis'=>$devis[$i],
            'cours' =>number_format($montCours, 2, ',', ' ')];
        }
        return $data;
    }


}