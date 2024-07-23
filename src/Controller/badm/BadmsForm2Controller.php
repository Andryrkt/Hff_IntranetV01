<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\Agence;
use App\Entity\Service;
use App\Form\BadmForm2Type;
use App\Entity\CasierValider;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\BadmsForm2Trait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsForm2Controller extends Controller
{
    use FormatageTrait;
    use BadmsForm2Trait;

    /**
     * @Route("/badm-form2", name="badms_newForm2")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $badm = new Badm();

        $form1Data = $this->sessionService->get('badmform1Data', []);

        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);

       /** INITIALISATION */
       $badm = $this->initialisation($badm, $form1Data, $data, self::$em);
      
       $form = self::$validator->createBuilder(BadmForm2Type::class, $badm)->getForm();
       
       $form->handleRequest($request);
       

            if($form->isSubmitted() && $form->isValid())
            {
                dd($badm);
            }
       self::$twig->display(
            'badm/secondForm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'items' => $data,
                'form1Data' => $form1Data,
                'form' => $form->createView()
            ]
        );
    }

     /**
     * @Route("/service-fetch/{id}", name="fetch_service", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire et casier destiantaireselon l'agence debiteur en ajax
     * @return void
     */
    public function agenceFetch(int $id)
    {
        $agence = self::$em->getRepository(Agence::class)->find($id);
  
        $service = $agence->getServices();

     
         $services = [];
       foreach ($service as $value) {
         $services[] = [
             'value' => $value->getId(),
             'text' => $value->getCodeService() . ' ' . $value->getLibelleService(),
         ];
       }

       header("Content-type:application/json");

        echo json_encode($services);
    }

    /**
     * @Route("/casier-fetch/{id}", name="fetch_casier", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service destinataire l'agence debiteur en ajax
     * @return void
     */
    public function casierFetch(int $id)
    {
        $agence = self::$em->getRepository(Agence::class)->find($id);
  
        $casier = $agence->getCasiers();

         $casiers = [];
       foreach ($casier as $value) {
         $casiers[] = [
             'value' => $value->getId(),
             'text' => $value->getCasier()
         ];
       }
       header("Content-type:application/json");

        echo json_encode($casiers);
    }
}