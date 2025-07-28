<?php

namespace App\Service\dit\transfer;

use App\Entity\admin\Agence;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;

class RecupDataAncienDitService
{
    private $em;

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
        $this->userRepository = $this->em->getRepository(User::class);
        $this->statutDemandeRepository = $this->em->getRepository(StatutDemande::class);
        $this->typeDocumentRepository = $this->em->getRepository(WorTypeDocument::class);
        $this->niveauUregenceRepository = $this->em->getRepository(WorNiveauUrgence::class);
        $this->categorieDemandeRepository = $this->em->getRepository(CategorieAteApp::class);
        $this->agenceRepository = $this->em->getRepository(Agence::class);
        $this->serviceRepository = $this->em->getRepository(Service::class);
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

            //AGENCE - SERVICE
            ->setAgenceServiceEmetteur($ancienDit['IDAgence'].'-'.$ancienDit['IDService'])
            ->setAgenceServiceDebiteur(null)
            //Agence et service emetteur debiteur ID
            ->setAgenceEmetteurId($this->agenceRepository->findOneBy(["codeAgence" => $ancienDit['IDAgence']]))
            ->setServiceEmetteurId($this->serviceRepository->findOneBy(["codeService" => $ancienDit['IDService']]))
            ->setAgenceDebiteurId(null)
            ->setServiceDebiteurId(null)

            //INFO CLIENT
            ->setNomClient($ancienDit['LibelleClient'])
            ->setNumeroTel(null)
            ->setClientSousContrat(null)
            ->setMailClient(null)
            ->setNumeroClient($ancienDit['NumeroClient'])


            //INFO DEMANDE
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

            //INFO DEMANDEUR
            ->setMailDemandeur('')
            ->setUtilisateurDemandeur($ancienDit['UtilisateurDemandeur'])

            //INFORMATION MATERIEL
            ->setIdMateriel($ancienDit['NumeroMateriel'])
            ->setKm($ancienDit['KilometrageMachine'])
            ->setHeure($ancienDit['HeureMachine'])

            //PIECE JOINT
            ->setPieceJoint01(null)
            ->setPieceJoint02(null)
            ->setPieceJoint03(null)

            //INFO OR
            ->setNumeroOR($ancienDit['NumeroOR'])
            ->setStatutOr('')
            ->setDateValidationOr(ConversionService::convertToDateTime($ancienDit['DateOR']))

            //INFO DEVIS
            ->setNumeroDevisRattache($ancienDit['NumeroOR'])
            ->setStatutDevis(null)

            //MIGRATION
            ->setMigration(1)
        ;

    }

    public function dataDevis(array $ancienDevis): array
    {
        $devisAnciens = [];
        foreach ($ancienDevis as $ancienDevi) {
            $devisAnciens[] = [
                'NumeroDit' => '',
                'NumeroDevis' => '',
                'NumeroItv' => '',
                'NombreLigneItv' => '',
                'MontantItv' => '',
                'NumeroVersion' => '',
                'MontantPiece' => '',
                'MontantMo' => '',
                'MontantAchatLocaux' => '',
                'MontantFraisDivers' => '',
                'MontantLubrifiants' => '',
                'LibellelItv' => '',
                'Statut' => '',
                'DateHeureSoumission' => '',
                'MontantForfait' => '',
                'NatureOperation' => '',
                'Devise' => '',
                'DevisVenteOuForfait' => '',
            ];
        }

        return $devisAnciens;
    }

    public function dataBc(array $ancienBcs): array
    {
        $bcAnciens = [];
        foreach ($ancienBcs as $ancienBc) {
            $bcAnciens[] = [
                'NumDit' => '',
                'NumDevis' => '',
                'NumBc' => '',
                'NumVersion' => '',
                'DateBc' => '',
                'DateDevis' => '',
                'MontantDevis' => '',
                'DateHeureSoumission' => '',
                'NomFichier' => '',
            ];
        }

        return $bcAnciens;
    }

    public function dataOr(array $ancienOrs): array
    {
        $orAnciens = [];
        foreach ($ancienOrs as $ancienOr) {
            $orAnciens[] = [
                'NumeroOR' => '',
                'NumeroItv' => '',
                'NombreLigneItv' => '',
                'MontantItv' => '',
                'NumeroVersion' => '',
                'MontantPiece' => '',
                'MontantMo' => '',
                'MontantAchatLocaux' => '',
                'MontantFraisDivers' => '',
                'MontantLubrifiants' => '',
                'LibellelItv' => '',
                'DateSoumission' => '',
                'HeureSoumission' => '',
                'Statut' => '',
                'Migration' => '',
            ];
        }

        return $orAnciens;
    }
}
