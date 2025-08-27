<?php

namespace App\Controller\Traits\da\creation;

use App\Model\da\DaModel;
use App\Entity\da\DemandeAppro;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;

trait DaNewAvecDitTrait
{
    use DaNewTrait;

    //=====================================================================================
    private DaModel $daModel;
    private DitRepository $ditRepository;
    private $fournisseurs;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daModel = new DaModel();
        $this->setAllFournisseurs();
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
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }

    /** 
     * Fonction pour retourner le nom du bouton cliqué
     *  - enregistrerBrouillon
     *  - soumissionAppro
     */
    private function getButtonName(Request $request): string
    {
        if ($request->request->has('enregistrerBrouillon')) {
            return 'enregistrerBrouillon';
        } elseif ($request->request->has('soumissionAppro')) {
            return 'soumissionAppro';
        } else {
            return '';
        }
    }

    /** 
     * Fonctions pour définir les fournisseurs dans le propriété $fournisseur
     */
    private function setAllFournisseurs()
    {
        $fournisseurs = $this->daModel->getAllFournisseur();
        $this->fournisseurs = array_column($fournisseurs, 'numerofournisseur', 'nomfournisseur');
    }
}
