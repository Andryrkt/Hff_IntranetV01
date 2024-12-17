<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Form\dom\DomForm2Type;
use App\Entity\admin\Personnel;
use App\Entity\admin\Application;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Service\genererPdf\GeneratePdfDom;
use App\Controller\Traits\dom\DomsDupliTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DomsDupliController extends Controller
{
    use FormatageTrait;
    use DomsDupliTrait;
    
   
    /**
     * @Route("/dom-dupli-form/{id}", name="dom_dupli_form")
     */
    public function secondForm(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $dom = new Dom();
        /** INITIALISATION des données  */
        //recupération des données qui vient du formulaire 1
        // $form1Data = $this->sessionService->get('form1Data', []);
        // $this->initialisationSecondForm($form1Data, self::$em, $dom);

        $dom = self::$em->getRepository(Dom::class)->find($id);
        dd($dom);
        $criteria = $this->criteria($form1Data, self::$em);

        $is_temporaire = $form1Data['salarier'];

        $form =self::$validator->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $domForm = $form->getData();

            $this->enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, self::$em);

        
            $verificationDateExistant = $this->verifierSiDateExistant($dom->getMatricule(),  $dom->getDateDebut(), $dom->getDateFin());
                
            if ($form1Data['salarier'] === "PERMANANT") 
            {
                    if ($form1Data['sousTypeDocument']->getCodeSousType() !== 'COMPLEMENT' ) 
                    {
                        if ($form1Data['sousTypeDocument']->getCodeSousType()  === 'FRAIS EXCEPTIONNEL') 
                        {
                                if ($verificationDateExistant) 
                                {
                                    $message = $dom->getMatricule() .' '. $dom->getNom() .' '. $dom->getPrenom() ." a déja une mission enregistrée sur ces dates, vérifier SVP!";
                                    $this->notification($message);
                                } else {
                                    $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                                }
                        } 

                        if ($verificationDateExistant) {
                            $message = $dom->getMatricule() .' '. $dom->getNom() .' '. $dom->getPrenom() . "  a déja une mission enregistrée sur ces dates, vérifier SVP!";

                            $this->notification($message);
                        } else {
                                if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                                    $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                                } 
                                else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    $this->notification($message);
                                }
                        }
                        
                    } else {
                            if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement()=== 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                            
                                $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                            } 
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                $this->notification($message);
                            }
                    } 
            } else {

                    if ($form1Data['sousTypeDocument'] !== 'COMPLEMENT') 
                    {
                        
                        if ($form1Data['sousTypeDocument'] === 'FRAIS EXCEPTIONNEL' && $dom->getDevis() !== 'MGA') 
                        {
                            if ($verificationDateExistant) 
                            {
                                $message = $dom->getMatricule() .' '. $dom->getNom() .' '. $dom->getPrenom() . "  a déja une mission enregistrée sur ces dates, vérifier SVP!";
                                $this->notification($message);
                            } else {
                                $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                            }
                            
                        }

                            if ($verificationDateExistant) 
                            {
                                $message = $dom->getMatricule() .' '. $dom->getNom() .' '. $dom->getPrenom() ."  a déja une mission enregistrée sur ces dates, vérifier SVP!";
                                $this->notification($message);
                            } else {
                                if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) 
                                {
                                    $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                                } else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";
                                    $this->notification($message);
                                }
                            }
                        
                    } else {

                        if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em);
                        } 
                        else {
                            $message = "Assurer que le Montant Total est supérieur ou égale à 500.000";

                            $this->notification($message);
                        }
                        
                    }
                
            }

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('domList_ShowListDomRecherche');
        }

        self::$twig->display('doms/dupli.html.twig', [
            'form' => $form->createView(),
            'is_temporaire' => $is_temporaire,
            'criteria' => $criteria
        ]);
    }


    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}