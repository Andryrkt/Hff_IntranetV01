<?php

namespace App\Service\dit\transfer;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\admin\utilisateur\UserRepository;
use App\Repository\admin\dit\CategorieAteAppRepository;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use App\Repository\admin\dit\WorTypeDocumentRepository;

class RecupDataAncienDitService
{
    private $userRepository;
    private $statutDemandeRepository;
    private $typeDocumentRepository;
    private $niveauUregenceRepository;
    private $categorieDemandeRepository;
    private $agenceRepository;
    private $serviceRepository;

    public function __construct(
        UserRepository $userRepository,
        StatutDemandeRepository $statutDemandeRepository,
        WorTypeDocumentRepository $typeDocumentRepository,
        WorNiveauUrgenceRepository $niveauUregenceRepository,
        CategorieAteAppRepository $categorieDemandeRepository,
        AgenceRepository $agenceRepository,
        ServiceRepository $serviceRepository
    ) {
        $this->userRepository = $userRepository;
        $this->statutDemandeRepository = $statutDemandeRepository;
        $this->typeDocumentRepository = $typeDocumentRepository;
        $this->niveauUregenceRepository = $niveauUregenceRepository;
        $this->categorieDemandeRepository = $categorieDemandeRepository;
        $this->agenceRepository = $agenceRepository;
        $this->serviceRepository = $serviceRepository;
    }

    public function ditEnObjet(array $ancienDit): DemandeIntervention
    {
        $dit = new DemandeIntervention();

        return $dit
            ->setNumeroDemandeIntervention($ancienDit['NumeroDemandeIntervention'])
            ->setTypeDocument($this->typeDocumentRepository->find(7))
            ->setTypeReparation('A REALISER')
            ->setReparationRealise('ATE TANA')
            ->setCategorieDemande($this->categorieDemandeRepository->find(2))
            ->setInternetExterne('EXTERNE')
            ->setAgenceServiceEmetteur($ancienDit['IDAgence'] . '-' . $ancienDit['IDService'])
            ->setAgenceServiceDebiteur(null)
            ->setAgenceEmetteurId($this->agenceRepository->findOneBy(["codeAgence" => $ancienDit['IDAgence']]))
            ->setServiceEmetteurId($this->serviceRepository->findOneBy(["codeService" => $ancienDit['IDService']]))
            ->setAgenceDebiteurId(null)
            ->setServiceDebiteurId(null)
            ->setNomClient($ancienDit['LibelleClient'])
            ->setNumeroTel(null)
            ->setClientSousContrat(null)
            ->setMailClient(null)
            ->setNumeroClient($ancienDit['NumeroClient'])
            ->setDatePrevueTravaux(new \DateTime())
            ->setDemandeDevis($ancienDit['DemandeDevis'])
            ->setIdNiveauUrgence($this->niveauUregenceRepository->find(1))
            ->setObjetDemande($ancienDit['ObjetDemande'])
            ->setDetailDemande($ancienDit['DetailDemande'])
            ->setLivraisonPartiel('NON')
            ->setIdStatutDemande($this->statutDemandeRepository->find(50))
            ->setAvisRecouvrement('NON')
            ->setDateDemande(ConversionService::convertToDateTime($ancienDit['DateDemande']))
            ->setHeureDemande(ConversionService::convertToHHMM($ancienDit['HeureDemande']))
            ->setMailDemandeur('')
            ->setUtilisateurDemandeur($ancienDit['UtilisateurDemandeur'])
            ->setIdMateriel($ancienDit['NumeroMateriel'])
            ->setKm($ancienDit['KilometrageMachine'])
            ->setHeure($ancienDit['HeureMachine'])
            ->setPieceJoint01(null)
            ->setPieceJoint02(null)
            ->setPieceJoint03(null)
            ->setNumeroOR($ancienDit['NumeroOR'])
            ->setStatutOr('')
            ->setDateValidationOr(ConversionService::convertToDateTime($ancienDit['DateOR']))
            ->setNumeroDevisRattache($ancienDit['NumeroOR'])
            ->setStatutDevis(null)
            ->setMigration(1);
    }

    // ... aled останалите методи остават същите
}
