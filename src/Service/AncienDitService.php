<?php

namespace App\Service;

error_reporting(E_ALL);
ini_set('display_errors', 1);


use App\Model\dit\DitModel;
use App\Entity\dit\AncienDit;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Traits\FormatageTrait;
use App\Service\genererPdf\GenererPdfDit;

class AncienDitService
{
    use FormatageTrait;

    private $em;

    private $ditModel;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->ditModel = new DitModel();
    }

    public function recupDesAncienDonnee($numDit)
    {
        //recuperation des données dans l'intranet ancien
        $ancienDit = $this->em->getRepository(AncienDit::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $demandeIntervention = new DemandeIntervention();
        //creation de l'entité dit pour la creation pdf
        $pdfDemandeInterventions = $this->pdfDemandeIntervention($ancienDit, $demandeIntervention);
        //recupération et transformation des historique du materiel
        $historiqueMateriel = $this->historiqueInterventionMateriel($ancienDit);

        //Initialisation du classe generate pdf
        $genererPdfDit = new GenererPdfDit();
        //générer le pdf de dit
        $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);
        $this->prepareFunsion($ancienDit);
        //envoyer le pdf dans docuware
        //$genererPdfDit->copyInterneToDOCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
        // $insertDemandeInterventions = $this->insertDemandeIntervention($ancienDit, $demandeIntervention);
        // $this->em->persist($insertDemandeInterventions);
        // $this->em->flush();
    }

    private function pdfDemandeIntervention($ancienDit, DemandeIntervention $demandeIntervention): DemandeIntervention
    {
        $demandeIntervention->setTypeDocument($ancienDit->getTypeDocument());
        //Objet - Detail
        $demandeIntervention->setObjetDemande($ancienDit->getObjetDemande());
        $demandeIntervention->setDetailDemande($ancienDit->getDetailDemande());
        //Categorie - avis recouvrement - devis demandé
        $demandeIntervention->setCategorieDemande($ancienDit->getCategorieDemande());
        $demandeIntervention->setAvisRecouvrement($ancienDit->getAvisRecouvrement());
        $demandeIntervention->setDemandeDevis($ancienDit->getDemandeDevis());

        //Intervention
        $demandeIntervention->setIdNiveauUrgence($ancienDit->getIdNiveauUrgence());
        $demandeIntervention->setDatePrevueTravaux($ancienDit->getDatePrevueTravaux());

        //Agence - service
        $demandeIntervention->setAgenceServiceEmetteur($ancienDit->getAgenceServiceEmetteur());
        $demandeIntervention->setAgenceServiceDebiteur($ancienDit->getAgenceServiceDebiteur());

        //REPARATION
        $demandeIntervention->setTypeReparation($ancienDit->getTypeReparation());
        $demandeIntervention->setReparationRealise($ancienDit->getReparationRealise());
        $demandeIntervention->setInternetExterne($ancienDit->getInternetExterne());

        //INFO CLIENT
        $demandeIntervention->setNomClient($ancienDit->getNomClient());
        $demandeIntervention->setNumeroTel($ancienDit->getNumeroTel());
        $demandeIntervention->setClientSousContrat($ancienDit->getClientSousContrat());


        $data = $this->ditModel->findAll($ancienDit->getIdMateriel(), $ancienDit->getNumParc(), $ancienDit->getNumSerie());

        //Caractéristiques du matériel
        $demandeIntervention->setNumParc($data[0]['num_parc']);
        $demandeIntervention->setNumSerie($data[0]['num_serie']);
        $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
        $demandeIntervention->setConstructeur($data[0]['constructeur']);
        $demandeIntervention->setModele($data[0]['modele']);
        $demandeIntervention->setDesignation($data[0]['designation']);
        $demandeIntervention->setCasier($data[0]['casier_emetteur']);
        $demandeIntervention->setLivraisonPartiel($ancienDit->getLivraisonPartiel());
        //Bilan financière
        $demandeIntervention->setCoutAcquisition($data[0]['prix_achat']);
        $demandeIntervention->setAmortissement($data[0]['amortissement']);
        $demandeIntervention->setChiffreAffaire($data[0]['chiffreaffaires']);
        $demandeIntervention->setChargeEntretient($data[0]['chargeentretien']);
        $demandeIntervention->setChargeLocative($data[0]['chargelocative']);
        //Etat machine
        $demandeIntervention->setKm($data[0]['km']);
        $demandeIntervention->setHeure($data[0]['heure']);



        //INFORMATION ENTRER MANUELEMENT
        $demandeIntervention->setNumeroDemandeIntervention($ancienDit->getNumeroDemandeIntervention());
        $demandeIntervention->setMailDemandeur($ancienDit->getMailDemandeur());
        $demandeIntervention->setDateDemande($ancienDit->getDateDemande());



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

    private function insertDemandeIntervention($dits, DemandeIntervention $demandeIntervention): DemandeIntervention
    {
        $demandeIntervention->setObjetDemande($dits->getObjetDemande());
        $demandeIntervention->setDetailDemande($dits->getDetailDemande());
        $demandeIntervention->setTypeDocument($dits->getTypeDocument());
        $demandeIntervention->setCategorieDemande($dits->getCategorieDemande());
        $demandeIntervention->setLivraisonPartiel($dits->getLivraisonPartiel());
        $demandeIntervention->setDemandeDevis($dits->getDemandeDevis());
        $demandeIntervention->setAvisRecouvrement($dits->getAvisRecouvrement());
        //AGENCE - SERVICE
        $demandeIntervention->setAgenceServiceEmetteur($dits->getAgenceServiceEmetteur());
        $demandeIntervention->setAgenceServiceDebiteur($dits->getAgenceServiceDebiteur());
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
        $demandeIntervention->setIdMateriel($dits->getIdMateriel());

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
        $demandeIntervention->setUtilisateurDemandeur($dits->getUtilisateurDemandeur());


        //Agence et service emetteur debiteur ID
        $demandeIntervention->setAgenceEmetteurId($dits->getAgenceEmetteurId());
        $demandeIntervention->setServiceEmetteurId($dits->getServiceEmetteurId());
        $demandeIntervention->setAgenceDebiteurId($dits->getAgenceDebiteurId());
        $demandeIntervention->setServiceDebiteurId($dits->getServiceDebiteurId());

        //societte

        return $demandeIntervention;
    }

    private function prepareFunsion($ancienDit)
    {

        if ($ancienDit->getPieceJoint01() != "" || $ancienDit->getPieceJoint02() != "" || $ancienDit->getPieceJoint03() != "") {

            $pdfFiles = [];
            if ($ancienDit->getPieceJoint01() != null || $ancienDit->getPieceJoint01() != "") {
                $pdfFiles[] = 'C:/wamp64/www/Upload/dit/fichier/' . str_replace(' ', '_', $ancienDit->getPieceJoint01());
                dump(str_replace(' ', '_', $ancienDit->getPieceJoint01()));
            }

            if ($ancienDit->getPieceJoint02() != null || $ancienDit->getPieceJoint02() != "") {
                $pdfFiles[] = 'C:/wamp64/www/Upload/dit/fichier/' . str_replace(' ', '_', $ancienDit->getPieceJoint02());
            }

            if ($ancienDit->getPieceJoint03() != null || $ancienDit->getPieceJoint03() != "") {
                $pdfFiles[] = 'C:/wamp64/www/Upload/dit/fichier/' . str_replace(' ', '_', $ancienDit->getPieceJoint03());
            }

            //ajouter le nom du pdf crée par dit en avant du tableau
            array_unshift($pdfFiles, 'C:/wamp64/www/Upload/dit/' . $ancienDit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $ancienDit->getAgenceServiceEmetteur()) . '.pdf');


            // Nom du fichier PDF fusionné
            $mergedPdfFile = 'C:/wamp64/www/Upload/dit/' . $ancienDit->getNumeroDemandeIntervention() . '_' . str_replace("-", "", $ancienDit->getAgenceServiceEmetteur()) . '.pdf';

            // Appeler la fonction pour fusionner les fichiers PDF
            if (!empty($pdfFiles)) {
                $fusionPdf = new FusionPdf();
                $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
            }
        }
    }
}
