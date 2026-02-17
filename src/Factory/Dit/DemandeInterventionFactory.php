<?php

namespace App\Factory\Dit;

use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Application;
use App\Dto\Dit\DemandeInterventionDto;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Service\autres\AutoIncDecService;
use App\Repository\admin\ApplicationRepository;
use App\Service\historiqueOperation\HistoriqueOperationDITService;

class DemandeInterventionFactory
{
    private $entityManager;
    private $ditModel;
    private $historiqueOperation;

    public function __construct(
        EntityManagerInterface $entityManager,
        DitModel $ditModel,
        HistoriqueOperationDITService $historiqueOperation
    ) {
        $this->entityManager = $entityManager;
        $this->ditModel = $ditModel;
        $this->historiqueOperation = $historiqueOperation;
    }

    public function createFromDto(DemandeInterventionDto $dto): DemandeIntervention
    {
        $demandeIntervention = new DemandeIntervention();

        //Objet - Detail
        $demandeIntervention->setObjetDemande($dto->objetDemande);
        $demandeIntervention->setDetailDemande($dto->detailDemande);

        // TYPE DE DOCUMENT 
        if ($dto->typeDocument) {
            $typeDocumentEntity = $this->entityManager->getRepository(WorTypeDocument::class)->findOneBy(['description' => $dto->typeDocument]);
            $demandeIntervention->setTypeDocument($typeDocumentEntity);
        } else {
            $demandeIntervention->setTypeDocument(null);
        }

        // CATEGORIE
        if ($dto->categorieDemande) {
            $categorieEntity = $this->entityManager->getRepository(CategorieAteApp::class)->findOneBy(['libelleCategorieAteApp' => $dto->categorieDemande]);
            $demandeIntervention->setCategorieDemande($categorieEntity);
        } else {
            $demandeIntervention->setCategorieDemande(null);
        }

        // Livraison partiel - demande de devis - avis de recouvrement
        $demandeIntervention->setLivraisonPartiel($dto->livraisonPartiel);
        $demandeIntervention->setDemandeDevis($dto->demandeDevis === null ? 'NON' : $dto->demandeDevis);
        $demandeIntervention->setAvisRecouvrement($dto->avisRecouvrement);

        // AGENCE - SERVICE
        $demandeIntervention->setAgenceServiceEmetteur(substr($dto->agenceEmetteur, 0, 2) . '-' . substr($dto->serviceEmetteur, 0, 3));
        if ($dto->agence === null) {
            $demandeIntervention->setAgenceServiceDebiteur(null);
        } else {
            $demandeIntervention->setAgenceServiceDebiteur($dto->agence->getCodeAgence() . '-' . $dto->service->getCodeService());
        }

        // INTERVENTION
        $demandeIntervention->setIdNiveauUrgence($dto->idNiveauUrgence);
        $demandeIntervention->setDatePrevueTravaux($dto->datePrevueTravaux);

        // REPARATION
        $demandeIntervention->setTypeReparation($dto->typeReparation);
        $demandeIntervention->setReparationRealise($dto->reparationRealise);
        $demandeIntervention->setInternetExterne($dto->internetExterne);

        // INFO CLIENT :  numero - nom - numero tel - mail - sous contrat
        $demandeIntervention->setNumeroClient($dto->numeroClient);
        $demandeIntervention->setNomClient($dto->nomClient);
        $demandeIntervention->setNumeroTel($dto->numeroTel);
        $demandeIntervention->setMailClient($dto->mailClient);
        $demandeIntervention->setClientSousContrat($dto->clientSousContrat);

        // INFORMATION MATERIEL
        if (!empty($dto->idMateriel) || !empty($dto->numParc) || !empty($dto->numSerie)) {
            $data = $this->ditModel->findAll($dto->idMateriel, $dto->numParc, $dto->numSerie);

            if (empty($data)) {
                $message = "Echec lors de l'enregistrement de la dit, ce matériel n'est pas enregistré dans IPS";
                $this->historiqueOperation->sendNotificationCreation($message, $dto->idMateriel . '-' . $dto->numParc . '-' . $dto->numSerie, 'dit_new');
            } else {
                $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
                // Caractéristiques du matériel pour PDF
                $demandeIntervention->setNumParc($data[0]['num_parc']);
                $demandeIntervention->setNumSerie($data[0]['num_serie']);
                $demandeIntervention->setConstructeur($data[0]['constructeur']);
                $demandeIntervention->setModele($data[0]['modele']);
                $demandeIntervention->setDesignation($data[0]['designation']);
                $demandeIntervention->setCasier($data[0]['casier_emetteur']);
                // Bilan financière pour PDF
                $demandeIntervention->setCoutAcquisition($data[0]['prix_achat']);
                $demandeIntervention->setAmortissement($data[0]['amortissement']);
                $demandeIntervention->setChiffreAffaire($data[0]['chiffreaffaires']);
                $demandeIntervention->setChargeEntretient($data[0]['chargeentretien']);
                $demandeIntervention->setChargeLocative($data[0]['chargelocative']);
                // Etat machine pour PDF
                $demandeIntervention->setKm($data[0]['km']);
                $demandeIntervention->setHeure($data[0]['heure']);
            }
        }

        // PIECE JOINT
        $demandeIntervention->setPieceJoint01($dto->pieceJoint01);
        $demandeIntervention->setPieceJoint02($dto->pieceJoint02);
        $demandeIntervention->setPieceJoint03($dto->pieceJoint03);

        // statut demande - numero DIT - email - non d'utilisateur - date - heure - societe
        $demandeIntervention->setIdStatutDemande($dto->idStatutDemande);
        //$demandeIntervention->setNumeroDemandeIntervention($dto->numeroDemandeIntervention);
        $demandeIntervention->setMailDemandeur($dto->mailDemandeur);
        $demandeIntervention->setUtilisateurDemandeur($dto->utilisateurDemandeur);
        $demandeIntervention->setDateDemande($dto->dateDemande);
        $demandeIntervention->setHeureDemande($dto->heureDemande);
        $demandeIntervention->setSociete($dto->societe);

        // Agence et service emetteur et debiteur ID
        $em = $this->entityManager;
        $demandeIntervention->setAgenceEmetteurId($em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($dto->agenceEmetteur, 0, 2)]));
        $demandeIntervention->setServiceEmetteurId($em->getRepository(Service::class)->findOneBy(['codeService' => substr($dto->serviceEmetteur, 0, 3)]));
        if ($dto->internetExterne === 'EXTERNE') {
            $demandeIntervention->setAgenceDebiteurId($em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($dto->agenceEmetteur, 0, 2)]));
            $demandeIntervention->setServiceDebiteurId($em->getRepository(Service::class)->findOneBy(['codeService' => substr($dto->serviceEmetteur, 0, 3)]));
        } else {
            $demandeIntervention->setAgenceDebiteurId($dto->agence);
            $demandeIntervention->setServiceDebiteurId($dto->service);
        }

        // avoir - refacturation
        $demandeIntervention->setEstDitAvoir($dto->estDitAvoir);
        $demandeIntervention->setEstDitRefacturation($dto->estDitRefacturation);

        return $demandeIntervention;
    }

    public function createFromDtoPol(DemandeInterventionDto $dto): DemandeIntervention
    {
        $demandeIntervention = new DemandeIntervention();

        //Objet - Detail
        $demandeIntervention->setObjetDemande($dto->objetDemande);
        $demandeIntervention->setDetailDemande($dto->detailDemande);

        // TYPE DE DOCUMENT (Changer)
        if ($dto->typeDocument) {
            $typeDocumentEntity = $this->entityManager->getRepository(WorTypeDocument::class)->find(WorTypeDocument::MAINTENANCE_CURATIVE); // !changé
            $demandeIntervention->setTypeDocument($typeDocumentEntity);
        } else {
            $demandeIntervention->setTypeDocument(null);
        }

        // CATEGORIE (changer)
        if ($dto->categorieDemande) {
            $categorieEntity = $this->entityManager->getRepository(CategorieAteApp::class)->find(CategorieAteApp::REPARATION);  // !changé
            $demandeIntervention->setCategorieDemande($categorieEntity);
        } else {
            $demandeIntervention->setCategorieDemande(null);
        }

        // Livraison partiel - demande de devis - avis de recouvrement
        $demandeIntervention->setLivraisonPartiel($dto->livraisonPartiel);
        $demandeIntervention->setDemandeDevis($dto->demandeDevis === null ? 'NON' : $dto->demandeDevis);
        $demandeIntervention->setAvisRecouvrement($dto->avisRecouvrement);

        // AGENCE - SERVICE
        $demandeIntervention->setAgenceServiceEmetteur(substr($dto->agenceEmetteur, 0, 2) . '-' . substr($dto->serviceEmetteur, 0, 3));
        if ($dto->agence === null) {
            $demandeIntervention->setAgenceServiceDebiteur(null);
        } else {
            $demandeIntervention->setAgenceServiceDebiteur($dto->agence->getCodeAgence() . '-' . $dto->service->getCodeService());
        }

        // INTERVENTION
        $demandeIntervention->setIdNiveauUrgence($dto->idNiveauUrgence);
        $demandeIntervention->setDatePrevueTravaux($dto->datePrevueTravaux);

        // REPARATION
        $demandeIntervention->setTypeReparation($dto->typeReparation);
        $demandeIntervention->setReparationRealise('ATE POL TANA'); // ?INFO: corrigé
        $demandeIntervention->setInternetExterne($dto->internetExterne);

        // INFO CLIENT :  numero - nom - numero tel - mail - sous contrat
        $demandeIntervention->setNumeroClient($dto->numeroClient);
        $demandeIntervention->setNomClient($dto->nomClient);
        $demandeIntervention->setNumeroTel($dto->numeroTel);
        $demandeIntervention->setMailClient($dto->mailClient);
        $demandeIntervention->setClientSousContrat($dto->clientSousContrat);

        // INFORMATION MATERIEL
        if (!empty($dto->idMateriel) || !empty($dto->numParc) || !empty($dto->numSerie)) {
            $data = $this->ditModel->findAll($dto->idMateriel, $dto->numParc, $dto->numSerie);

            if (empty($data)) {
                $message = "Echec lors de l'enregistrement de la dit, ce matériel n'est pas enregistré dans IPS";
                $this->historiqueOperation->sendNotificationCreation($message, $dto->idMateriel . '-' . $dto->numParc . '-' . $dto->numSerie, 'dit_new');
            } else {
                $demandeIntervention->setIdMateriel($data[0]['num_matricule']);
                // Caractéristiques du matériel pour PDF
                $demandeIntervention->setNumParc($data[0]['num_parc']);
                $demandeIntervention->setNumSerie($data[0]['num_serie']);
                $demandeIntervention->setConstructeur($data[0]['constructeur']);
                $demandeIntervention->setModele($data[0]['modele']);
                $demandeIntervention->setDesignation($data[0]['designation']);
                $demandeIntervention->setCasier($data[0]['casier_emetteur']);
                // Bilan financière pour PDF
                $demandeIntervention->setCoutAcquisition($data[0]['prix_achat']);
                $demandeIntervention->setAmortissement($data[0]['amortissement']);
                $demandeIntervention->setChiffreAffaire($data[0]['chiffreaffaires']);
                $demandeIntervention->setChargeEntretient($data[0]['chargeentretien']);
                $demandeIntervention->setChargeLocative($data[0]['chargelocative']);
                // Etat machine pour PDF
                $demandeIntervention->setKm($data[0]['km']);
                $demandeIntervention->setHeure($data[0]['heure']);
            }
        }

        // PIECE JOINT
        $demandeIntervention->setPieceJoint01($dto->pieceJoint01);
        $demandeIntervention->setPieceJoint02($dto->pieceJoint02);
        $demandeIntervention->setPieceJoint03($dto->pieceJoint03);

        // statut demande - numero DIT - email - non d'utilisateur - date - heure
        $demandeIntervention->setIdStatutDemande($dto->idStatutDemande);
        //$demandeIntervention->setNumeroDemandeIntervention(AutoIncDecService::autoGenerateNumero(DemandeIntervention::CODE_APP, $dto->numeroDemandeIntervention, false)); // !changé
        $demandeIntervention->setMailDemandeur($dto->mailDemandeur);
        $demandeIntervention->setUtilisateurDemandeur($dto->utilisateurDemandeur);
        $demandeIntervention->setDateDemande($dto->dateDemande);
        $demandeIntervention->setHeureDemande($dto->heureDemande);

        // Agence et service emetteur et debiteur ID
        $em = $this->entityManager;
        $demandeIntervention->setAgenceEmetteurId($em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($dto->agenceEmetteur, 0, 2)]));
        $demandeIntervention->setServiceEmetteurId($em->getRepository(Service::class)->findOneBy(['codeService' => substr($dto->serviceEmetteur, 0, 3)]));
        if ($dto->internetExterne === 'EXTERNE') {
            $demandeIntervention->setAgenceDebiteurId($em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($dto->agenceEmetteur, 0, 2)]));
            $demandeIntervention->setServiceDebiteurId($em->getRepository(Service::class)->findOneBy(['codeService' => substr($dto->serviceEmetteur, 0, 3)]));
        } else {
            $demandeIntervention->setAgenceDebiteurId($dto->agence);
            $demandeIntervention->setServiceDebiteurId($dto->service);
        }

        // avoir - refacturation
        $demandeIntervention->setEstDitAvoir($dto->estDitAvoir);
        $demandeIntervention->setEstDitRefacturation($dto->estDitRefacturation);

        return $demandeIntervention;
    }
}
