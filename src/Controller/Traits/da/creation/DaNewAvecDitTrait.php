<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Model\da\DaModel;
use App\Repository\dit\DitRepository;

trait DaNewAvecDitTrait
{
    use DaNewTrait;

    //=====================================================================================
    private DaModel $daModel;
    private DitRepository $ditRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daModel = new DaModel();
    }
    //=====================================================================================
    /** 
     * Initialisation des valeurs par défaut pour une Demande d'Achat avec DIT
     * 
     * @param DemandeIntervention $dit DIT associé à la demande d'achat
     * 
     * @return DemandeAppro Retourne une instance de DemandeAppro initialisée
     */
    private function initialisationDemandeApproAvecDit(DemandeIntervention $dit): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $demandeAppro
            ->setDit($dit)
            ->setNiveauUrgence($dit->getIdNiveauUrgence()->getDescription())
            ->setObjetDal($dit->getObjetDemande())
            ->setDetailDal($dit->getDetailDemande())
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }

    /** 
     * Méthode pour envoyer une email pour la création d'une DA avec DIT
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    public function envoyerMailcreationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailcreationDaAvecDit($demandeAppro, $tab);
    }
}
