<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Application;
use App\Form\BadmForm2Type;
use App\Entity\CasierValider;
use App\Entity\StatutDemande;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\BadmsForm2Trait;
use App\Service\genererPdf\GenererPdfBadm;
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
         
                $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
                $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
                $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
                $badm
                ->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService())
                ->setCasierEmetteur($data[0]["casier_emetteur"]);
                $badm->setTypeMouvement(self::$em->getRepository(TypeMouvement::class)->find($badm->getTypeMouvement()));
                $idTypeMouvement = $badm->getTypeMouvement()->getId();
                if( $idTypeMouvement === 1) {
                    $agencedestinataire = $form->getData()->getAgence();
                    $serviceDestinataire = $form->getData()->getService();
                    $casierDestinataire = $form->getData()->getCasierDestinataire();
                    $dateMiseLocation = $form->getData()->getDateMiseLocation();
                } elseif ($idTypeMouvement === 2) {
                    $agencedestinataire = $form->getData()->getAgence();
                    $serviceDestinataire = $form->getData()->getService();
                    $casierDestinataire = $form->getData()->getCasierDestinataire();
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif ($idTypeMouvement === 3) {
                    if(in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                        $agencedestinataire = self::$em->getRepository(Agence::class)->find(9);
                        $serviceDestinataire = self::$em->getRepository(Service::class)->find(2);
                    } else {
                        $agencedestinataire = self::$em->getRepository(Agence::class)->find(1);
                        $serviceDestinataire = self::$em->getRepository(Service::class)->find(2);
                    }
                    $casierDestinataire = $form->getData()->getCasierDestinataire();
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif ($idTypeMouvement === 4) {
                    $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
                    $serviceDestinataire = $serviceEmetteur;
                    $casierDestinataire = self::$em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif($idTypeMouvement === 5) {
                    $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
                    $serviceDestinataire = $serviceEmetteur;
                    $casierDestinataire = self::$em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                }

                $badm
                ->setNumParc($data[0]["num_parc"])
                ->setHeureMachine((int)$data[0]['heure'])
                ->setKmMachine((int)$data[0]['km'])
                ->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]))
                ->setCoutAcquisition((float)$data[0]["droits_taxe"])
                ->setAmortissement((float)$data[0]["amortissement"])
                ->setValeurNetComptable((float)$data[0]["droits_taxe"] - $data[0]["amortissement"])
                ->setAgence($agencedestinataire)
                ->setService($serviceDestinataire)
                ->setCasierDestinataire($casierDestinataire)
                ->setDateMiseLocation($dateMiseLocation)
                ->setStatutDemande(self::$em->getRepository(StatutDemande::class)->find(15))
                ->setHeureDemande($this->getTime())
                ->setNumBadm($this->autoINcriment('BDM'))
                ->setAgenceServiceEmetteur(substr($badm->getAgenceEmetteur(),0,2) . substr($badm->getServiceEmetteur(),0,3))
                ->setAgenceServiceDestinataire($badm->getAgence()->getCodeAgence() . $badm->getService()->getCodeService())
                ->setNomUtilisateur(self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'))->getNomUtilisateur())
                ;
                

                //recuperation des ordres de réparation
                $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);
                if (empty($orDb)) {
                    $OR = 'NON';
                } else {
                    $OR = 'OUI';
                }
                foreach ($orDb as $keys => $values) {
                    foreach ($values as $key => $value) {
                        //var_dump($key === 'date');
                        if ($key == "date") {
                            // $or1["Date"] = implode('/', array_reverse(explode("-", $value)));
                            $orDb[$keys]['date'] = implode('/', array_reverse(explode("-", $value)));
                        } elseif ($key == 'agence_service') {
                            $orDb[$keys]['agence_service'] = trim(explode('-', $value)[0]);
                        } elseif ($key === 'montant_total' || $key === 'montant_pieces' || $key === 'montant_pieces_livrees') {
                            $orDb[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                        }
                    }
                }


                //condition
                $coditionAgenceService = $badm->getAgenceEmetteur() === $badm->getAgence() && $badm->getServiceEmetteur() === $badm->getService();
                $conditionAgenceServices = $badm->getAgence() === null && $badm->getService() === null || $coditionAgenceService;
                $conditionVide = $badm->getAgence() === null && $badm->getService() === null && $badm->getCasierDestinataire() === null && $badm->getDateMiseLocation() === null;
                $idMateriel = (int)$data[0]['num_matricule'];
                $idMateriels = self::$em->getRepository(Badm::class)->findIdMateriel();
            
            if (($idTypeMouvement === 1 || $idTypeMouvement === 2) && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';
                $this->alertRedirection($message);
            } 
            elseif ($idTypeMouvement === 1 && in_array($idMateriel, $idMateriels)) {
                $message = 'ce matériel est déjà en PARC';
                $this->alertRedirection($message);
            } elseif ($idTypeMouvement === 2 && $coditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } elseif ($idTypeMouvement === 2 && $conditionAgenceServices) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } else {

                //envoie des pièce jointe dans une dossier et le fusionner
            $this->envoiePieceJoint($form, $badm);

                $generPdfBadm = [
                    'typeMouvement' => $badm->getTypeMouvement()->getDescription(),
                    'Num_BDM' => $badm->getNumBadm(),
                    'Date_Demande' => $badm->getDateDemande()->format('d/m/Y'),
                    'Designation' => $data[0]['designation'],
                    'Num_ID' => $data[0]['num_matricule'],
                    'Num_Serie' => $data[0]['num_serie'],
                    'Groupe' => $data[0]['famille'],
                    'Num_Parc' => $badm->getNumParc(),
                    'Affectation' => $data[0]['affectation'],
                    'Constructeur' => $data[0]['constructeur'],
                    'Date_Achat' => $this->formatageDate($data[0]['date_achat']),
                    'Annee_Model' => $data[0]['annee'],
                    'Modele' => $data[0]['modele'],
                    'Agence_Service_Emetteur' => substr($badm->getAgenceEmetteur(),0,2) . '-' . substr($badm->getServiceEmetteur(),0,3),
                    'Casier_Emetteur' => $badm->getCasierEmetteur(),
                    'Agence_Service_Destinataire' => $badm->getAgence()->getCodeAgence() . '-' . $badm->getService()->getCodeService(),
                    'Casier_Destinataire' => $badm->getCasierDestinataire()->getCasier(),
                    'Motif_Arret_Materiel' => $badm->getMotifMateriel(),
                    'Etat_Achat' => $badm->getEtatAchat(),
                    'Date_Mise_Location' => $badm->getDateMiseLocation()->format('d/m/Y'),
                    'Cout_Acquisition' => (float)$badm->getCoutAcquisition(),
                    'Amort' => (float)$data[0]['amortissement'],
                    'VNC' => (float)$badm->getValeurNetComptable(),
                    'Nom_Client' => $badm->getNomClient(),
                    'Modalite_Paiement' => $badm->getModalitePaiement(),
                    'Prix_HT' => $badm->getPrixVenteHt(),
                    'Motif_Mise_Rebut' => $badm->getMotifMiseRebut(),
                    'Heures_Machine' => $data[0]['heure'],
                    'Kilometrage' => $data[0]['km'],
                    'Email_Emetteur' => self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'))->getMail(),
                    'Agence_Service_Emetteur_Non_separer' => substr($badm->getAgenceEmetteur(),0,2) . substr($badm->getServiceEmetteur(),0,3),
                    'image' => $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/bdm/fichiers/' . $badm->getNumBadm() . '.' . $form->get("nomImage")->getData()->getClientOriginalExtension(),
                    'extension' => strtoupper($form->get("nomImage")->getData()->getClientOriginalExtension()),
                    'OR' => $OR
                ];
               
                 
           
                //ENVOIE DANS LE BASE DE DONNEE
                self::$em->persist($badm);
                self::$em->flush();
                /** CREATION PDF */
                $createPdf = new GenererPdfBadm();
                $createPdf->genererPdfBadm($generPdfBadm, $orDb);
                $createPdf->copyInterneToDOXCUWARE($badm->getNumBadm(), substr($badm->getAgenceEmetteur(),0,2) . substr($badm->getServiceEmetteur(),0,3));
              
               
                //RECUPERATION de la dernière NumeroDemandeIntervention 
              $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM']);
              $application->setDerniereId($badm->getNumBadm());
                // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
                self::$em->persist($application);
                self::$em->flush();
                

                $this->redirectToRoute("badmListe_AffichageListeBadm");
            }
                
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