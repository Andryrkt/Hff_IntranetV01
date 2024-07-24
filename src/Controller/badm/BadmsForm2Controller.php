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
use App\Entity\StatutDemande;
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
                dump($form->getData());
                $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
                $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
                $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
                $badm->setServiceEmetteur($serviceEmetteur->getCodeService(). ' ' . $serviceEmetteur->getLibelleService())
                ->setCasierEmetteur($data[0]["casier_emetteur"]);
                            $idTypeMouvement = $badm->getTypeMouvement()->getId();
                if( $idTypeMouvement === 1) {
                    $agencedestinataire = null;
                    $serviceEmetteur = null;
                    $casierDestinataire = null;
                    $dateMiseLocation = null;
                    
                } elseif ($idTypeMouvement === 2) {
                    $agencedestinataire = null;
                    $serviceEmetteur = null;
                $casierDestinataire = null;
                $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif ($idTypeMouvement === 3) {
                        if(in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                            $agencedestinataire = self::$em->getRepository(Agence::class)->find(9);
                            $serviceEmetteur = self::$em->getRepository(Service::class)->find(2);
                        } else {
                            $agencedestinataire = self::$em->getRepository(Agence::class)->find(1);
                            $serviceEmetteur = self::$em->getRepository(Service::class)->find(2);
                        }
                    $casierDestinataire = null;
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif ($idTypeMouvement === 4) {
                    $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
                    $casierDestinataire = self::$em->getRepository(CasierValider::class)->findOneBy(['casier' => $data[0]["casier_emetteur"]]);
                    $dateMiseLocation =\DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
                } elseif($idTypeMouvement === 5) {
                    $agencedestinataire = self::$em->getRepository(Agence::class)->find($agenceEmetteur->getId());
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
                ->setService($serviceEmetteur)
                ->setCasierDestinataire($casierDestinataire)
                ->setDateMiseLocation($dateMiseLocation)
                ->setStatutDemande(self::$em->getRepository(StatutDemande::class)->find(15))
                ->setHeureDemande($this->getTime())
                ;
                dd($badm);

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

                    $conditionAgenceService = $badm->getAgence() === null && $badm->getService() === null || $badm->getAgenceEmetteur() === $badm->getServiceEmetteur();
            $conditionVide = $badm->getAgence() === null && $badm->getService() === null && $badm->getCasierDestinataire() === null && $badm->getDateMiseLocation() === null;

            
            if (($codeMouvement === 'ENTREE EN PARC' || $codeMouvement === 'CHANGEMENT AGENCE/SERVICE') && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';
                $this->alertRedirection($message);
            } 
            elseif ($codeMouvement === 'ENTREE EN PARC' && in_array($idMateriel, $idMateriels)) {
                $message = 'ce matériel est déjà en PARC';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && $agenceServiceEmetteur === $agenceServiceDestinataire) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && $conditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } else {

                $insertDbBadm = [
                    'Numero_Demande_BADM' => $NumBDM,
                    'Code_Mouvement' => $idTypeMouvement['ID_Type_Mouvement'],
                    'ID_Materiel' => $badm->getIdMateriel(),
                    'Nom_Session_Utilisateur' => $_SESSION['user'],
                    'Date_Demande' => $badm->getDateDemande(),
                    'Heure_Demande' => $badm->getHeureDemande(),
                    'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                    'Casier_Emetteur' => $casierEmetteur,
                    'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                    'Casier_Destinataire' => $casierDestinataire,
                    'Motif_Arret_Materiel' => $motifArretMateriel,
                    'Etat_Achat' => $etatAchat,
                    'Date_Mise_Location' => $dateMiseLocation,
                    'Cout_Acquisition' => (float)$coutAcquisition,
                    'Amortissement' => (float)$data[0]['amortissement'],
                    'Valeur_Net_Comptable' => (float) str_replace(',', '.', $this->formatNumber($vnc)),
                    'Nom_Client'  => $nomClient,
                    'Modalite_Paiement'  => $modalitePaiement,
                    'Prix_Vente_HT'  => (float)$prixHt,
                    'Motif_Mise_Rebut'  => $motifMiseRebut,
                    'Heure_machine'  => (int)$data[0]['heure'],
                    'KM_machine'  => (int)$data[0]['km'],
                    'Code_Statut' => 'OUV',
                    'Num_Parc' => $numParc,
                    'Nom_Image' => $image,
                    'Nom_Fichier' => $fichier,
                    'ID_Statut_Demande' => (int)$idStatut
                ];
                foreach ($insertDbBadm as $cle => $valeur) {
                    $insertDbBadm[$cle] = strtoupper($valeur);
                }

               
                //var_dump($insertDbBadm);
                // die();
                if ($codeMouvement === 'CESSION D\'\'ACTIF') {
                    $codeMouvement = 'CESSION D\'ACTIF';
                }

                $generPdfBadm = [
                    'typeMouvement' => $codeMouvement,
                    'Num_BDM' => $NumBDM,
                    'Date_Demande' => $this->formatageDate($dateDemande),
                    'Designation' => $data[0]['designation'],
                    'Num_ID' => $data[0]['num_matricule'],
                    'Num_Serie' => $data[0]['num_serie'],
                    'Groupe' => $data[0]['famille'],
                    'Num_Parc' => $numParc,
                    'Affectation' => $data[0]['affectation'],
                    'Constructeur' => $data[0]['constructeur'],
                    'Date_Achat' => $this->formatageDate($data[0]['date_achat']),
                    'Annee_Model' => $data[0]['annee'],
                    'Modele' => $data[0]['modele'],
                    'Agence_Service_Emetteur' => $agenceEmetteur . '-' . $serviceEmetteur,
                    'Casier_Emetteur' => $casierEmetteur,
                    'Agence_Service_Destinataire' => $agenceDestinataire . '-' . $serviceDestinataire,
                    'Casier_Destinataire' => $casierDestinataire,
                    'Motif_Arret_Materiel' => $motifArretMateriel,
                    'Etat_Achat' => $etatAchat,
                    'Date_Mise_Location' => $this->formatageDate($dateMiseLocation),
                    'Cout_Acquisition' => $this->formatNumber($coutAcquisition),
                    'Amort' => $this->formatNumber($data[0]['amortissement']),
                    'VNC' => $this->formatNumber($vnc),
                    'Nom_Client' => $nomClient,
                    'Modalite_Paiement' => $modalitePaiement,
                    'Prix_HT' => $this->formatNumber($prixHt),
                    'Motif_Mise_Rebut' => $motifMiseRebut,
                    'Heures_Machine' => $this->formatNumber($data[0]['heure']),
                    'Kilometrage' => $this->formatNumber($data[0]['km']),
                    'Email_Emetteur' => $MailUser,
                    'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur,
                    'image' => $image,
                    'extension' => $extension,
                    'OR' => $OR
                ];
                // $generPdfBadm = $this->convertirEnUtf8($generPdfBadm);
                // var_dump($this->convertirEnUtf8($insertDbBadm));
                // die();
                
                $insertDbBadm = $this->convertirEnUtf8($insertDbBadm);
                $this->badm->insererDansBaseDeDonnees($insertDbBadm);
                $this->genererPdf->genererPdfBadm($generPdfBadm, $orDb);
                $this->genererPdf->copyInterneToDOXCUWARE($NumBDM, $agenceEmetteur . $serviceEmetteur);
                $this->badm->modificationDernierIdApp($NumBDM, 'BDM');
                header('Location: /Hffintranet/listBadm');
                exit();
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