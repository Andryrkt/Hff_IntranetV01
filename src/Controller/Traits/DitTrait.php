<?php

namespace App\Controller\Traits;

use App\Entity\admin\Agence;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;

trait DitTrait
{
    private function demandeDevis($dits)
    {
        return $dits->getDemandeDevis() === null ? 'NON' : $dits->getDemandeDevis();
    }

    private function insertDemandeIntervention($dits, DemandeIntervention $demandeIntervention, $em): DemandeIntervention
    {

        $demandeIntervention->setObjetDemande($dits->getObjetDemande());
        $demandeIntervention->setDetailDemande($dits->getDetailDemande());
        $demandeIntervention->setTypeDocument($dits->getTypeDocument());
        $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
        $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
        $demandeIntervention->setDemandeDevis($this->demandeDevis($dits));
        $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
        //AGENCE - SERVICE
        $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).'-'.substr($dits->getServiceEmetteur(), 0, 3));
        if ($dits->getAgence() === null) {
            $demandeIntervention->setAgenceServiceDebiteur(null);
        } else {
            $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().'-'. $dits->getService()->getCodeService());
        }
        //INTERVENTION
        $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
        $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());
        //REPARATION
        $demandeIntervention->setTypeReparation($dits->getTypeReparation());
        $demandeIntervention->setReparationRealise($dits->getReparationRealise());
        $demandeIntervention->setInternetExterne($dits->getInternetExterne());
        //INFO CLIENT
        $demandeIntervention->setNomClient($dits->getNomClient());
        $demandeIntervention->setNumeroTel($dits->getNumeroTel());
        $demandeIntervention->setClientSousContrat($dits->getClientSousContrat());
        //INFORMATION MATERIEL
        if (! empty($dits->getIdMateriel()) || ! empty($dits->getNumParc()) || ! empty($dits->getNumSerie())) {
            $data = $this->ditModel->findAll($dits->getIdMateriel(), $dits->getNumParc(), $dits->getNumSerie());
            if (empty($data)) {
                $message = 'ce matériel n\'est pas enregistrer dans Irium';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_new');
            } else {
                $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
            }
        }
        //PIECE JOINT
        $demandeIntervention->setPieceJoint01($dits->getPieceJoint01());
        $demandeIntervention->setPieceJoint02($dits->getPieceJoint02());
        $demandeIntervention->setPieceJoint03($dits->getPieceJoint03());

        //INFORMATION ENTRER MANUELEMENT
        $demandeIntervention->setIdStatutDemande($dits->getIdStatutDemande());
        $demandeIntervention->setNumeroDemandeIntervention($dits->getNumeroDemandeIntervention());
        $demandeIntervention->setMailDemandeur($dits->getMailDemandeur());
        $demandeIntervention->setDateDemande($dits->getDateDemande());
        $demandeIntervention->setHeureDemande($dits->getHeureDemande());
        $demandeIntervention->getUtilisateurDemandeur($dits->getUtilisateurDemandeur());


        //Agence et service emetteur debiteur ID
        $demandeIntervention->setAgenceEmetteurId($em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($dits->getAgenceEmetteur(), 0, 2)]));
        $demandeIntervention->setServiceEmetteurId($em->getRepository(Service::class)->findOneBy(['codeService' => substr($dits->getServiceEmetteur(), 0, 3)]));
        $demandeIntervention->setAgenceDebiteurId($dits->getAgence());
        $demandeIntervention->setServiceDebiteurId($dits->getService());

        //societte
        // dd($demandeIntervention);
        return $demandeIntervention;
    }

    private function pdfDemandeIntervention($dits, DemandeIntervention $demandeIntervention): DemandeIntervention
    {

        //Objet - Detail
        $demandeIntervention->setObjetDemande($dits->getObjetDemande());
        $demandeIntervention->setDetailDemande($dits->getDetailDemande());
        //Categorie - avis recouvrement - devis demandé
        $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
        $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
        $demandeIntervention->setDemandeDevis($this->demandeDevis($dits));

        //Intervention
        $demandeIntervention->setIdNiveauUrgence($dits->getIdNiveauUrgence());
        $demandeIntervention->setDatePrevueTravaux($dits->getDatePrevueTravaux());

        //Agence - service
        $demandeIntervention->setAgenceServiceEmetteur(substr($dits->getAgenceEmetteur(), 0, 2).'-'.substr($dits->getServiceEmetteur(), 0, 3));
        if ($dits->getAgence() === null) {
            $demandeIntervention->setAgenceServiceDebiteur(null);
        } else {
            $demandeIntervention->setAgenceServiceDebiteur($dits->getAgence()->getCodeAgence().'-'. $dits->getService()->getCodeService());
        }

        //REPARATION
        $demandeIntervention->setTypeReparation($dits->getTypeReparation());
        $demandeIntervention->setReparationRealise($dits->getReparationRealise());
        $demandeIntervention->setInternetExterne($dits->getInternetExterne());

        //INFO CLIENT
        $demandeIntervention->setNomClient($dits->getNomClient());
        $demandeIntervention->setNumeroTel($dits->getNumeroTel());
        $demandeIntervention->setMailClient($dits->getMailClient());

        if (! empty($dits->getIdMateriel()) || ! empty($dits->getNumParc()) || ! empty($dits->getNumSerie())) {

            $data = $this->ditModel->findAll($dits->getIdMateriel(), $dits->getNumParc(), $dits->getNumSerie());
            if (empty($data)) {
                $message = 'ce matériel n\'est pas enregistrer dans Irium';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_new');
            } else {
                //Caractéristiques du matériel
                $demandeIntervention->setNumParc($data[0]['num_parc']);
                $demandeIntervention->setNumSerie($data[0]['num_serie']);
                $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
                $demandeIntervention->setConstructeur($data[0]['constructeur']);
                $demandeIntervention->setModele($data[0]['modele']);
                $demandeIntervention->setDesignation($data[0]['designation']);
                $demandeIntervention->setCasier($data[0]['casier_emetteur']);
                $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
                //Bilan financière
                $demandeIntervention->setCoutAcquisition($data[0]['prix_achat']);
                $demandeIntervention->setAmortissement($data[0]['amortissement']);
                $demandeIntervention->setChiffreAffaire($data[0]['chiffreaffaires']);
                $demandeIntervention->setChargeEntretient($data[0]['chargeentretien']);
                $demandeIntervention->setChargeLocative($data[0]['chargelocative']);
                //Etat machine
                $demandeIntervention->setKm($data[0]['km']);
                $demandeIntervention->setHeure($data[0]['heure']);
            }
        }

        //INFORMATION ENTRER MANUELEMENT
        $demandeIntervention->setNumeroDemandeIntervention($dits->getNumeroDemandeIntervention());
        $demandeIntervention->setMailDemandeur($dits->getMailDemandeur());
        $demandeIntervention->setDateDemande($dits->getDateDemande());



        return $demandeIntervention;
    }

    private function historiqueInterventionMateriel($dits): array
    {
        $historiqueMateriel = $this->ditModel->historiqueMateriel($dits->getIdMateriel());
        foreach ($historiqueMateriel as $keys => $values) {
            foreach ($values as $key => $value) {
                if ($key == "datedebut") {
                    $historiqueMateriel[$keys]['datedebut'] = implode('/', array_reverse(explode("-", $value)));
                } elseif ($key === 'somme') {
                    $historiqueMateriel[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                }
            }
        }

        return $historiqueMateriel;
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     * @param [type] $form
     * @param [type] $dits
     * @param [type] $nomFichier
     * @return void
     */
    private function uplodeFile($form, $dits, $nomFichier, &$pdfFiles)
    {

        /** @var UploadedFile $file */
        $file = $form->get($nomFichier)->getData();
        $fileName = $dits->getNumeroDemandeIntervention(). '_0'. substr($nomFichier, -1, 1) . '.' . $file->getClientOriginalExtension();

        $fileDossier = $_ENV['BASE_PATH_FICHIER'].'/dit/fichier/';
        //$fileDossier = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\PRODUCTION\\DIT\\';
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }

        $setPieceJoint = 'set'.ucfirst($nomFichier);
        $dits->$setPieceJoint($fileName);


    }

    private function envoiePieceJoint($form, $dits, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i = 1; $i < 4; $i++) {
            $nom = "pieceJoint0{$i}";
            if ($form->get($nom)->getData() !== null) {
                $this->uplodeFile($form, $dits, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_ENV['BASE_PATH_FICHIER'].'/dit/' . $dits->getNumeroDemandeIntervention(). '_' . str_replace("-", "", $dits->getAgenceServiceEmetteur()). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_ENV['BASE_PATH_FICHIER'].'/dit/' . $dits->getNumeroDemandeIntervention(). '_' . str_replace("-", "", $dits->getAgenceServiceEmetteur()). '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (! empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    /**
     * INFO AJOUTER MANUELEMENT des entités DANS LA CLASSE DEMANDE D'INTERVENTION
     *
     * @param [type] $form
     * @param [type] $em
     * @return DemandeIntervention
     */
    private function infoEntrerManuel($form, $em, $user): DemandeIntervention
    {
        $dits = $form->getData();

        $dits->setUtilisateurDemandeur($user->getNomUtilisateur());
        $dits->setHeureDemande($this->getTime());
        $dits->setDateDemande(new \DateTime($this->getDatesystem()));
        $statutDemande = $em->getRepository(StatutDemande::class)->find(50);
        $dits->setIdStatutDemande($statutDemande);
        $dits->setNumeroDemandeIntervention($this->autoDecrementDIT('DIT'));
        $email = $em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $user->getNomUtilisateur()])->getMail();
        $dits->setMailDemandeur($email);

        return $dits;
    }

    /**
     * INITIALISER LA VALEUR DE LA FORMULAIRE
     *
     * @param DemandeIntervention $demandeIntervention
     * @param [type] $em
     * @return void
     */
    private function initialisationForm(DemandeIntervention $demandeIntervention, $em)
    {
        $agenceService = $this->agenceServiceIpsObjet();

        $demandeIntervention->setAgenceEmetteur($agenceService['agenceIps']->getCodeAgence() . ' '. $agenceService['agenceIps']->getLibelleAgence());
        $demandeIntervention->setServiceEmetteur($agenceService['serviceIps']->getCodeService() . ' ' . $agenceService['serviceIps']->getLibelleService());
        $demandeIntervention->setAgence($agenceService['agenceIps']);
        $demandeIntervention->setService($agenceService['serviceIps']);
        $demandeIntervention->setIdNiveauUrgence($em->getRepository(WorNiveauUrgence::class)->find(1));
    }
}
