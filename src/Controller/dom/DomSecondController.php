<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Entity\admin\Personnel;
use App\Entity\admin\Application;
use App\Entity\admin\StatutDemande;
use App\Controller\Traits\DomsTrait;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomSecondController extends Controller
{
    use FormatageTrait;
    use DomsTrait;
    
   
      /**
     * @Route("/dom-second-form", name="dom_second_form")
     */
    public function secondForm(Request $request)
    {
        $dom = new Dom();
        /** INITIALISATION des données  */
        //recupération des données qui vient du formulaire 1
        $form1Data = $this->sessionService->get('form1Data', []);
        $this->initialisationSecondForm($form1Data, self::$em, $dom);
        

        $is_temporaire = $form1Data['salarier'];

    
        $form =self::$validator->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $domForm = $form->getData();

            $statutDemande = self::$em->getRepository(StatutDemande::class)->find(1);
            if($domForm->getModePayement() === 'MOBILE MONEY'){
                $mode = $form->get('mode')->getData();
                $numTel = $form->get('mode')->getData();
            } else if($domForm->getModePayement() === 'VIREMENT BANCAIRE') {
                $mode = $form->get('mode')->getData();
                $numTel ='';
            } else {
                $mode = '';
                $numTel = '';
            }
            $agenceDebiteur = $domForm->getAgence();
            $serviceDebiteur= $domForm->getService();
            $agenceEmetteur= self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($domForm->getAgenceEmetteur(),0,2)]);
            $serviceEmetteur= self::$em->getRepository(Service::class)->findOneBy(['codeService' => substr($domForm->getServiceEmetteur(),0,3)]);
            $supplementJournaliere = $form->get('supplementJournaliere')->getData();
        
            if ($form1Data['salarier'] === "TEMPORAIRE") {
                $dom->setNom($form1Data['nom']);
                $dom->setPrenom($form1Data['prenom']);
                $dom->setCin($form1Data['cin']);
            } else {
                $personnel = self::$em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
                $dom->setNom($personnel->getNom());
                $dom->setPrenom($personnel->getPrenoms());
            }

            $dom
            ->setTypeDocument($form1Data['sousTypeDocument']->getCodeDocument())
                ->setSousTypeDocument($form1Data['sousTypeDocument'])
                ->setCategorie($form1Data['categorie'])
                ->setMatricule($form1Data['matricule'])
                ->setUtilisateurCreation($_SESSION['user'])
                ->setNomSessionUtilisateur($_SESSION['user'])
                ->setNumeroOrdreMission($this->autoINcriment('DIT'))
                ->setIdStatutDemande($statutDemande)
                ->setCodeAgenceServiceDebiteur($agenceDebiteur->getCodeagence().$serviceDebiteur->getCodeService())
                ->setModePayement($domForm->getModePayement().':'.$mode)
                ->setCodeStatut($statutDemande->getCodeStatut())
                ->setNumeroTel($numTel)
                ->setLibelleCodeAgenceService($agenceEmetteur->getLibelleAgence().'-'.$serviceEmetteur->getLibelleService())
                ->setDroitIndemnite($supplementJournaliere)
                ->setAgenceEmetteurId($agenceEmetteur)
                ->setServiceEmetteurId($serviceEmetteur)
                ->setAgenceDebiteurId($agenceDebiteur)
                ->setServiceDebiteurId($serviceDebiteur)
                ->setCategoryId($domForm->getCategorie())
                ->setSiteId($domForm->getSite())
            ;
        
        
            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
            $application->setDerniereId($dom->getNumeroOrdreMission());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();

            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            // self::$em->persist($dom->getCategorie());
            // self::$em->persist($dom->getSousTypeDocument());
            self::$em->persist($dom);
      
            self::$em->flush();

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('some_success_route');
        }

        self::$twig->display('doms/secondForm.html.twig', [
            'form' => $form->createView(),
            'is_temporaire' => $is_temporaire
        ]);
    }

}