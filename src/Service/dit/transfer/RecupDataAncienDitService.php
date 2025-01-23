<?php

namespace App\Service\dit\transfer;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\Service;
use App\Model\dit\transfer\AncienDitExterneModel;

class RecupDataAncienDitService
{
    private $em;
    private $ancienDitExternModel;
    private $userRepository;
    private $statutDemandeRepository;
    private $typeDocumentRepository;
    private $niveauUregenceRepository;
    private $categorieDemandeRepository;
    private $agenceRepository;
    private $serviceRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->ancienDitExternModel = new AncienDitExterneModel();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->statutDemandeRepository = $this->em->getRepository(StatutDemande::class);
        $this->typeDocumentRepository = $this->em->getRepository(WorTypeDocument::class);
        $this->niveauUregenceRepository = $this->em->getRepository(WorNiveauUrgence::class);
        $this->categorieDemandeRepository = $this->em->getRepository(CategorieAteApp::class);
        $this->agenceRepository = $this->em->getRepository(Agence::class);
        $this->serviceRepository = $this->em->getRepository(Service::class);
    }

    public function dataDit(): array
    {
        $ancienDits = $this->ancienDitExternModel->recupDit();
        $ditAnciens = [];
        foreach ($ancienDits as $ancienDit) {
            $ditAnciens[] = [
                'NumeroDemandeIntervention' => $ancienDit['NumeroDemandeIntervention'],
                'TypeDocument'              => $this->typeDocumentRepository->find(7),
                
                'TypeReparation'            => 'A REALISER',
                'ReparationRealise'         => 'ATE TANA',
                
                'CategorieDemande'          => $this->categorieDemandeRepository->find(2),
                'InternetExterne'           => 'EXTERNE',
    
                //AGENCE - SERVICE
                'AgenceServiceEmetteur'     => $ancienDit['IDAgence'].'-'.$ancienDit['IDService'],
                'AgenceServiceDebiteur'     => null,
                //Agence et service emetteur debiteur ID
                'AgenceEmetteurId'          => $this->agenceRepository->findOneBy(["codeAgence" => $ancienDit['IDAgence']]) ,
                'ServiceEmetteurId'         => $this->serviceRepository->findOneBy(["codeService" => $ancienDit['IDService']]),
                'AgenceDebiteurId'          => null,
                'ServiceDebiteurId'         => null,
    
                //INFO CLIENT
                'NomClient'                 => $ancienDit['LibelleClient'],
                'NumeroTel'                 => null,
                'ClientSousContrat'         => null,
                'MailClient'                => null,
                'NumeroClient'              => $ancienDit['NumeroClient'],
    
    
                //INFO DEMANDE
                'DatePrevueTravaux'         => new \DateTime(),
                'DemandeDevis'              => $ancienDit['DemandeDevis'],
                'IdNiveauUrgence'           => $this->niveauUregenceRepository->find(1),
                'ObjetDemande'              => $ancienDit['ObjetDemande'],
                'DetailDemande'             => $ancienDit['DetailDemande'],
                'LivraisonPartiel'          => 'NON',
    
                'IdStatutDemande'           => $this->statutDemandeRepository->find(50),
                'AvisRecouvrement'          => 'NON',
                'DateDemande'               => $this->convertToDateTime($ancienDit['DateDemande']),
                'HeureDemande'              => $this->convertToHHMM($ancienDit['HeureDemande']),
    
                //INFO DEMANDEUR
                // 'MailDemandeur'             => $this->userRepository->findMail($ancienDit['UtilisateurDemandeur']),
                'MailDemandeur'             => '',
                'UtilisateurDemandeur'      => $ancienDit['UtilisateurDemandeur'],
    
                //INFORMATION MATERIEL
                'IdMateriel'                => $ancienDit['NumeroMateriel'],
                'Km'                        => $ancienDit['KilometrageMachine'],
                'Heure'                     => $ancienDit['HeureMachine'],
    
                //PIECE JOINT
                'PieceJoint01'              => null,
                'PieceJoint02'              => null,
                'PieceJoint03'              => null,
    
                //INFO OR
                'NumeroOR'                  => $ancienDit['NumeroOR'],
                'StatutOr'                  => null,
                'DateValidationOr'          => $this->convertToDateTime($ancienDit['DateOR']),
    
                //INFO DEVIS
                'NumeroDevisRattache'       => $ancienDit['NumeroOR'],
                'StatutDevis'               => null,
            ]; 
        }
        return $ditAnciens;
    }



    private function convertToDateTime(string $dateString, string $format = 'Y-m-d'): ?\DateTime
    {
        $dateTime = \DateTime::createFromFormat($format, $dateString);
        return ($dateTime && $dateTime->format($format) === $dateString) ? $dateTime : null;
    }

    private function convertToHHMM(string $time)
    {
        // Convertit le temps en chaîne de 6 caractères si ce n'est pas déjà le cas
        $time = str_pad($time, 6, "0", STR_PAD_LEFT);

        // Récupère les heures et minutes
        $hours = substr($time, 0, 2);
        $minutes = substr($time, 2, 2);

        // Format final
        return $hours . ":" . $minutes;
    }
}