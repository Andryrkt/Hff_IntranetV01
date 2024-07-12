<?php

namespace App\Controller;

use App\Entity\Dom;
use App\Form\DomForm1Type;
use App\Form\DomForm2Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomsController extends Controller
{
    /**
     * @Route("/dom-first-form", name="dom_first_form")
     */
    public function firstForm(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $dom = new Dom();
        $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
    $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
    
    $dom->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']) );
    $dom->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']));
   $dom->setSalarier('PERMANENT');
        $form =self::$validator->createBuilder(DomForm1Type::class, $dom)->getForm();

        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Logique pour traiter les données ici si nécessaire

            // Redirection vers le second formulaire
            return $this->redirectToRoute('dom_second_form');
        }
        
        self::$twig->display('doms/firstForm.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
        ]);
    }

    /**
     * @Route("/dom-second-form", name="dom_second_form")
     */
    public function secondForm(Request $request)
    {
        $form =self::$validator->createBuilder(DomForm2Type::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici, vous pouvez enregistrer les données dans la base de données si nécessaire

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('some_success_route');
        }

        self::$twig->display('form/secondForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}