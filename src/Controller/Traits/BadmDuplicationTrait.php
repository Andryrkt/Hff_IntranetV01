<?php

namespace App\Controller\Traits;

use App\Entity\badm\Badm;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\cas\CasierValider;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;

trait BadmDuplicationTrait
{
    private function changeEtatAchat($dataEtatAchat)
    {
        if ($dataEtatAchat === 'N') {
            return 'NEUF';
        } else {
            return 'OCCASION';
        }
    }

    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("badms_newForm1");
    }

    private function dateMiseEnlocation($data)
    {
        if ($data[0]["date_location"] === null) {
            $dateMiseLocation = null;
        } else {

            $dateMiseLocation = \DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
        }
        return $dateMiseLocation;
    }

    private function initialisation(Badm $badm, $form1Data, $data, $em): Badm
    {
        $badm
            ->setTypeMouvement($form1Data)
            //caracteristique du materiel
            ->setGroupe($data[0]["famille"])
            ->setAffectation($data[0]["affectation"])
            ->setConstructeur($data[0]["constructeur"])
            ->setDesignation($data[0]["designation"])
            ->setModele($data[0]["modele"])
            ->setNumParc($data[0]["num_parc"])
            ->setNumSerie($data[0]["num_serie"])
            ->setIdMateriel((int)$data[0]["num_matricule"])
            ->setAnneeDuModele($data[0]["annee"])
            ->setDateAchat($this->formatageDate($data[0]["date_achat"]))
            //etat machine
            ->setHeureMachine((int)$data[0]['heure'])
            ->setKmMachine((int)$data[0]['km'])
            //Agence - service - casier Emetteur
        ;
        $idTypeMouvement = $badm->getTypeMouvement()->getId();
        $agenceEmetteur = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
        $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
        $serviceEmetteur = $em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
        if ($data[0]["casier_emetteur"] === null) {
            $casierEmetteur = '';
        } else {
            $casierEmetteur =  $data[0]["casier_emetteur"];
        }


        //Agence - service - casier destinataire
        if ($idTypeMouvement === 1) {
            $agencedestinataire = null;
            $serviceDestinataire = null;
            $casierDestinataire = null;
            $dateMiseLocation = null;
            $serviceEmetteur = $em->getRepository(Service::class)->find(2);
        } elseif ($idTypeMouvement === 2) {
            $agencedestinataire = null;
            $serviceDestinataire = null;
            $casierDestinataire = null;
            //$serviceEmetteur = $em->getRepository(Service::class)->find(2);
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 3) {
            $agencedestinataire = $agenceEmetteur;
            $serviceDestinataire = $serviceEmetteur;
            $casierDestinataire = null;
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 4) {
            if (in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                $agencedestinataire = $em->getRepository(Agence::class)->find(9);
                $serviceDestinataire = $em->getRepository(Service::class)->find(2);
            } else {
                $agencedestinataire = $em->getRepository(Agence::class)->find(1);
                $serviceDestinataire = $em->getRepository(Service::class)->find(2);
            }
            $casierDestinataire = null;
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 5) {
            $agencedestinataire = $agenceEmetteur;
            $serviceDestinataire = $serviceEmetteur;
            $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $casierEmetteur]);
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        }

        $badm->setAgence($agencedestinataire);
        $badm->setService($serviceDestinataire);
        $badm->setCasierDestinataire($casierDestinataire);
        $badm->setDateMiseLocation($dateMiseLocation);
        $badm->setServiceEmetteur($serviceEmetteur->getCodeService() . ' ' . $serviceEmetteur->getLibelleService());
        $badm->setCasierEmetteur($casierEmetteur);

        //ENTREE EN PARC
        $badm->setEtatAchat($this->changeEtatAchat($data[0]["mmat_nouo"]));

        //BILAN FINANCIERE
        $badm->setCoutAcquisition((float)$data[0]["droits_taxe"])
            ->setAmortissement((float)$data[0]["amortissement"])
            ->setValeurNetComptable((float)$data[0]["droits_taxe"] - $data[0]["amortissement"])

            //date de demande
            ->setDateDemande(new \DateTime())
        ;

        return $badm;
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     * 
     */
    private function uplodeFile($form, $badm, $nomFichier)
    {

        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = $badm->getNumBadm() . '.' . $file->getClientOriginalExtension();

        $fileDossier = $_ENV['BASE_PATH_FICHIER'] . '/bdm/fichiers/';

        $file->move($fileDossier, $fileName);

        $setPieceJoint = 'set' . ucfirst($nomFichier);
        $badm->$setPieceJoint($fileName);
    }

    private function envoiePieceJoint($form, $badm)
    {

        if ($form->get("nomImage")->getData() !== null) {
            $this->uplodeFile($form, $badm, "nomImage");
        }

        if ($form->get("nomFichier")->getData() !== null) {
            $this->uplodeFile($form, $badm, "nomFichier");
        }
    }


    private function ouiNonOr($orDb): string
    {
        if (empty($orDb)) {
            $OR = 'NON';
        } else {
            $OR = 'OUI';
        }

        return $OR;
    }

    private function miseEnformeOrDb($orDb)
    {
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

        return $orDb;
    }

    private function ajoutDesDonnnerFormulaire($data, $em, $badm, $form, $idTypeMouvement)
    {
        $agenceEmetteur = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]]);
        $badm->setAgenceEmetteur(($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence()));
        $serviceEmetteur = $em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]]);
        if ($data[0]["casier_emetteur"] === null) {
            $casierEmetteur = '';
        } else {
            $casierEmetteur =  $data[0]["casier_emetteur"];
        }


        if ($idTypeMouvement === 1) {
            $agencedestinataire = $form->getData()->getAgence();
            $serviceDestinataire = $form->getData()->getService();
            $casierDestinataire = $form->getData()->getCasierDestinataire();
            $dateMiseLocation = $form->getData()->getDateMiseLocation();
            $serviceEmetteur = $em->getRepository(Service::class)->find(2);
        } elseif ($idTypeMouvement === 2) {
            $agencedestinataire = $form->getData()->getAgence();
            $serviceDestinataire = $form->getData()->getService();
            $casierDestinataire = $form->getData()->getCasierDestinataire();
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 3) {
            $agencedestinataire = $agenceEmetteur;
            $serviceDestinataire = $serviceEmetteur;
            $casierDestinataire = $form->getData()->getCasierDestinataire();
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 4) {
            if (in_array($agenceEmetteur->getId(), [9, 10, 11])) {
                $agencedestinataire = $em->getRepository(Agence::class)->find(9);
                $serviceDestinataire = $em->getRepository(Service::class)->find(2);
            } else {
                $agencedestinataire = $em->getRepository(Agence::class)->find(1);
                $serviceDestinataire = $em->getRepository(Service::class)->find(2);
            }
            $casierDestinataire = null;
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        } elseif ($idTypeMouvement === 5) {
            $agencedestinataire = $agenceEmetteur;
            $serviceDestinataire = $serviceEmetteur;
            $casierDestinataire = $em->getRepository(CasierValider::class)->findOneBy(['casier' => $casierEmetteur]);
            $dateMiseLocation = $this->dateMiseEnlocation($data);
        }

        // if ($data[0]['code_affect'] === 'LCD') {
        //     $dateMiseLocation = \DateTime::createFromFormat('Y-m-d', $data[0]["date_location"]);
        // } else {
        //     $dateMiseLocation = null;
        // }

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
            ->setStatutDemande($em->getRepository(StatutDemande::class)->find(15))
            ->setHeureDemande($this->getTime())
            ->setNumBadm($this->autoINcriment('BDM'))
            ->setAgenceServiceEmetteur(substr($badm->getAgenceEmetteur(), 0, 2) . substr($badm->getServiceEmetteur(), 0, 3))
            ->setAgenceServiceDestinataire($badm->getAgence()->getCodeAgence() . $badm->getService()->getCodeService())
            ->setNomUtilisateur($this->getUserName())
            ->setServiceEmetteur($serviceEmetteur->getCodeService() . ' ' . $serviceEmetteur->getLibelleService())
            ->setCasierEmetteur($casierEmetteur)
        ;
    }

    private function genereteTabPdf($OR, $data, $badm, $form, $em, $idTypeMouvement)
    {
        if ($idTypeMouvement === 1 || $idTypeMouvement === 2 || $idTypeMouvement === 3 || $idTypeMouvement === 4) {
            $image = '';
            $extension = '';
        } elseif ($idTypeMouvement === 5) {
            $image =  $_ENV['BASE_PATH_FICHIER'] . '/bdm/fichiers/' . $badm->getNumBadm() . '.' . $form->get("nomImage")->getData()->getClientOriginalExtension();
            $extension = strtoupper($form->get("nomImage")->getData()->getClientOriginalExtension());
        }


        $generPdfBadm = [
            'typeMouvement'                       => $badm->getTypeMouvement()->getDescription(),
            'Num_BDM'                             => $badm->getNumBadm(),
            'Date_Demande'                        => $badm->getDateDemande()->format('d/m/Y'),
            'Designation'                         => $data[0]['designation'],
            'Num_ID'                              => $data[0]['num_matricule'],
            'Num_Serie'                           => $data[0]['num_serie'],
            'Groupe'                              => $data[0]['famille'],
            'Num_Parc'                            => $badm->getNumParc(),
            'Affectation'                         => $data[0]['affectation'],
            'Constructeur'                        => $data[0]['constructeur'],
            'Date_Achat'                          => $this->formatageDate($data[0]['date_achat']),
            'Annee_Model'                         => $data[0]['annee'],
            'Modele'                              => $data[0]['modele'],
            'Agence_Service_Emetteur'             => substr($badm->getAgenceEmetteur(), 0, 2) . '-' . substr($badm->getServiceEmetteur(), 0, 3),
            'Casier_Emetteur'                     => $badm->getCasierEmetteur(),
            'Agence_Service_Destinataire'         => $badm->getAgence()->getCodeAgence() . '-' . $badm->getService()->getCodeService(),
            'Casier_Destinataire'                 => $badm->getCasierDestinataire() === null ? '' : $badm->getCasierDestinataire()->getCasier(),
            'Motif_Arret_Materiel'                => $badm->getMotifMateriel(),
            'Etat_Achat'                          => $badm->getEtatAchat(),
            'Date_Mise_Location'                  => $badm->getDateMiseLocation() === null ? '' : $badm->getDateMiseLocation()->format('d/m/Y'),
            'Cout_Acquisition'                    => (float)$badm->getCoutAcquisition(),
            'Amort'                               => (float)$data[0]['amortissement'],
            'VNC'                                 => (float)$badm->getValeurNetComptable(),
            'Nom_Client'                          => $badm->getNomClient(),
            'Modalite_Paiement'                   => $badm->getModalitePaiement(),
            'Prix_HT'                             => $badm->getPrixVenteHt(),
            'Motif_Mise_Rebut'                    => $badm->getMotifMiseRebut(),
            'Heures_Machine'                      => $data[0]['heure'],
            'Kilometrage'                         => $data[0]['km'],
            'Email_Emetteur'                      => $this->getUserMail(),
            'Agence_Service_Emetteur_Non_separer' => substr($badm->getAgenceEmetteur(), 0, 2) . substr($badm->getServiceEmetteur(), 0, 3),
            'image'                               => $image,
            'extension'                           => $extension,
            'OR'                                  => $OR
        ];

        return $generPdfBadm;
    }
}
