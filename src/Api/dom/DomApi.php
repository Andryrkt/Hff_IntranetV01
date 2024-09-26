<?php

namespace App\Api\dom;

use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Controller\Controller;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\dom\Indemnite;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use Symfony\Component\Routing\Annotation\Route;

class DomApi extends Controller
{
    use FormatageTrait;
    
    /**
     * @Route("/categorie-fetch/{id}", name="fetch_categorie", methods={"GET"})
     * 
     * Cette fonction permet d'envoier les donner de categorie selon la sousType de document
     *
     * @param int $id
     * @return void
     */
    public function categoriefetch(int $id)
    {   $this->SessionStart();
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        $sousTypedocument = self::$em->getRepository(SousTypeDocument::class)->find($id);
        if($CodeServiceofCours[0]['agence_ips'] === '50'){
            $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
           
       } else {
        $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
       }
    
       $criteria = [
        'sousTypeDoc' => $sousTypedocument,
        'rmq' => $rmq
     ];

        
     $catg = self::$em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);
 

        header("Content-type:application/json");

        echo json_encode($catg);
    }

     /**
     * @Route("/matricule-fetch/{id}", name="fetch_matricule", methods={"GET"})
     * 
     * Cette fonction permet d'envoier les donner de categorie selon la sousType de document
     *
     * @param int $id
     * @return void
     */
    public function matriculeFetch(int $id)
    {
        $personnel = self::$em->getRepository(Personnel::class)->find($id)->toArray();
     

        header("Content-type:application/json");

        echo json_encode($personnel);
    }


    /**
     * @Route("/form1Data-fetch", name="fetch_form1Data", methods={"GET"})
     *permet d'envoyer les donnner du form1
     * @return void
     */
    public function form1DataFetch()
    {
        $form1Data = $this->sessionService->get('form1Data', []);
        header("Content-type:application/json");

        echo json_encode($form1Data);
    }

    /**
     * @Route("/agence-fetch/{id}", name="fetch_agence", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service debiteur selon l'agence debiteur en ajax
     * @return void
     */
    public function agence($id) {
        $agence = self::$em->getRepository(Agence::class)->find($id);
    
    $service = $agence->getServices();

    //   $services = $service->getValues();
        $services = [];
    foreach ($service as $key => $value) {
        $services[] = [
            'value' => $value->getId(),
            'text' => $value->getCodeService() . ' ' . $value->getLibelleService()
        ];
    }

    header("Content-type:application/json");

    echo json_encode($services);
    }

    /**
     * @Route("/site-idemnite-fetch/{id}", name="fetch_siteIdemnite", methods={"GET"})
     *
     * @return void
     */
    public function siteIndemniteFetch(int $id)
    {
        $site = self::$em->getRepository(Site::class)->find($id);
        $montant = self::$em->getRepository(Indemnite::class)->findOneBy(['site' => $site])->getMontant();

        $montant = $this->formatNumber($montant);

        header("Content-type:application/json");

        echo json_encode(['montant' => $montant]);
    }

    /**
     * @Route("/personnel-fetch/{matricule}", name="fetch_personnel", methods={"GET"})
     *
     * @param [type] $matricule
     * @return void
     */
    public function personnelFetch($matricule){
        $personne = self::$em->getRepository(Personnel::class)->findOneBy(['Matricule' => $matricule]);
        
        $tab = [
            'compteBancaire' => $personne->getNumeroCompteBancaire(),
        ];

        header("Content-type:application/json");

        echo json_encode($tab);
    }
}