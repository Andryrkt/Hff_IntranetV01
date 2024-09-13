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
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\SousTypeDocument;
use App\Service\genererPdf\GeneratePdfDom;
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
                if (substr($form->get('mode')->getData(),0,4) === '+261') {
                    $numTel = str_replace('+261', '0', $form->get('mode')->getData());
                    $mode = str_replace('+261', '0',$form->get('mode')->getData());
                } else {
                    $numTel = $form->get('mode')->getData();
                    $mode = $form->get('mode')->getData();
                }
                
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

            $sousTypeDocument = self::$em->getRepository(SousTypeDocument::class)->find($form1Data['sousTypeDocument']->getId());
            if (isset($form1Data['categorie'])) {
                $categoryId = self::$em->getRepository(Catg::class)->find($form1Data['categorie']->getId());
            } else {
                $categoryId = null;
            }

            if($form1Data['salarier'] === 'TEMPORAIRE'){
                $cin = $form1Data["cin"];
                 $matricule = "XER00 -" . $cin . " - TEMPORAIRE";
            } else {
                    $matricule = $form1Data['matricule'];
            }
            
            dd($form1Data);
            if ($form1Data['sousTypeDocument']->getCodeSousType() === 'FRAIS EXCEPTIONNEL') {
                $site = self::$em->getRepository(Site::class)->find(1);
            } else {
                $site = $domForm->getSite();
            }

            $dom
                ->setTypeDocument($form1Data['sousTypeDocument']->getCodeDocument())
                ->setSousTypeDocument($sousTypeDocument)
                ->setCategorie($categoryId)
                ->setMatricule($matricule)
                ->setUtilisateurCreation($_SESSION['user'])
                ->setNomSessionUtilisateur($_SESSION['user'])
                ->setNumeroOrdreMission($this->autoINcriment('DOM'))
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
                ->setCategoryId($categoryId)
                ->setSiteId($site)
                ->setHeureDebut($domForm->getHeureDebut()->format('H:i'))
                ->setHeureFin($domForm->getHeureFin()->format('H:i'))
                ->setEmetteur($domForm->getAgenceEmetteur().'-'.$domForm->getServiceEmetteur())
                ->setDebiteur($domForm->getAgence()->getLibelleAgence().'-'.$domForm->getService()->getLibelleService())
            ;
        

            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DOM']);
            $application->setDerniereId($dom->getNumeroOrdreMission());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();

            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            // self::$em->persist($dom->getCategorie());
            // self::$em->persist($dom->getSousTypeDocument());

            self::$em->persist($dom);
      
            self::$em->flush();

            if(explode(':',$dom->getModePayement())[0] === 'MOBILE MONEY' || explode(':',$dom->getModePayement())[0] === 'ESPECE'){
                $mode = 'TEL'.explode(':',$dom->getModePayement())[1];
            } else if(explode(':',$dom->getModePayement())[0] === 'VIREMENT BANCAIRE'){
                $mode = 'CPT'.explode(':',$dom->getModePayement())[1];
            }

            

            $email = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $_SESSION['user']])->getMail();
            $tabInternePdf = [
                "Devis" => $dom->getDevis(),
                "Prenoms" => $dom->getPrenom(),
                "AllMontant" => $dom->getTotalGeneralPayer(),
                "Code_serv" => $dom->getAgenceEmetteur(),
                "dateS" => $dom->getDateDemande()->format("d/m/Y"),
                "NumDom" => $dom->getNumeroOrdreMission(),
                "serv" => $dom->getServiceEmetteur(),
                "matr" => $dom->getMatricule(),
                "typMiss" => $dom->getSousTypeDocument()->getCodeSousType(),

                "Nom" => $dom->getNom(),
                "NbJ" => $dom->getNombreJour(),
                "dateD" => $dom->getDateDebut()->format("d/m/Y"),
                "heureD" => $dom->getHeureDebut(),
                "dateF" => $dom->getDateFin(),
                "heureF" => $dom->getHeureFin(),
                "motif" => $dom->getMotifDeplacement(),
                "Client" => $dom->getClient(),
                "fiche" => $dom->getFiche(),
                "lieu" => $dom->getLieuIntervention(),
                "vehicule" => $dom->getVehiculeSociete(),
                "numvehicul" => $dom->getNumVehicule(),
                "idemn" => $dom->getIndemniteForfaitaire(),
                "totalIdemn" => $dom->getTotalIndemniteForfaitaire(),
                "motifdep01" => $dom->getMotifAutresDepense1(),
                "montdep01" => $dom->getAutresDepense1(),
                "motifdep02" => $dom->getMotifAutresDepense2(),
                "montdep02" => $dom->getAutresDepense2(),
                "motifdep03" => $dom->getMotifAutresDepense3(),
                "montdep03" => $dom->getAutresDepense3(),
                "totaldep" => $dom->getTotalAutresDepenses(),
                "libmodepaie" => explode(':',$dom->getModePayement())[0],
                "mode" => $mode,
                "codeAg_serv" => substr($domForm->getAgenceEmetteur(),0,2).substr($domForm->getServiceEmetteur(),0,3),
                "CategoriePers" => $dom->getCategorie() === null ? '' : $dom->getCategorie()->getDescription(),
                "Site" => $dom->getSite() === null ? '' : $dom->getSite()->getNomZone(),
                "Idemn_depl" => $dom->getIdemnityDepl(),
                "MailUser" => $email,
                "Bonus" => $dom->getDroitIndemnite(),
                "codeServiceDebitteur" => $dom->getAgence()->getCodeAgence(),
                "serviceDebitteur" => $dom->getService()->getCodeService()
            ];

            $genererPdfDom = new GeneratePdfDom();
            $genererPdfDom->genererPDF($tabInternePdf);

            $this->envoiePieceJoint($form, $dom, $this->fusionPdf);

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('domList_ShowListDomRecherche');
        }

        self::$twig->display('doms/secondForm.html.twig', [
            'form' => $form->createView(),
            'is_temporaire' => $is_temporaire
        ]);
    }

}