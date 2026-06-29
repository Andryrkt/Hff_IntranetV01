<?php

namespace App\Controller\Traits\da\creation;

use DateTime;
use App\Model\da\DaModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\atelierRealise\AtelierRealise;
use App\Entity\da\DemandeAppro;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Repository\atelierRealise\AtelierRealiseRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Traits\JoursOuvrablesTrait;

trait DaNewAvecDitTrait
{
    use DaNewTrait, JoursOuvrablesTrait;

    //=====================================================================================
    private DaModel $daModel;
    private DitRepository $ditRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private $fournisseurs;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->setAllFournisseurs();
    }
    //=====================================================================================

    /** 
     * Initialisation des valeurs par défaut pour une Demande d'Achat avec DIT
     * 
     * @param DemandeIntervention $dit DIT associé à la demande d'achat
     * 
     * @return DemandeAppro Retourne une instance de DemandeAppro insitialisée
     */
    private function initialisationDemandeApproAvecDit(DemandeIntervention $dit): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        /** @var array{agenceIps:Agence,serviceIps:Service} */
        $agenceServiceEmetteur = $this->agenceServiceIpsObjet();
        $agenceEmetteur = $agenceServiceEmetteur['agenceIps'];
        $serviceEmetteur = $agenceServiceEmetteur['serviceIps'];

        $agenceServiceDebiteur = $this->handleAgenceEtServiceDebiteur($dit);
        $agenceDebiteur = $agenceServiceDebiteur['agence'];
        $serviceDebiteur = $agenceServiceDebiteur['service'];

        $demandeAppro
            ->setDaTypeId(DemandeAppro::TYPE_DA_AVEC_DIT)
            ->setNiveauUrgence($dit->getIdNiveauUrgence()->getDescription())
            ->setObjetDal($dit->getObjetDemande())
            ->setDetailDal($dit->getDetailDemande())
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceEmetteur($agenceEmetteur)
            ->setServiceEmetteur($serviceEmetteur)
            ->setAgenceServiceEmetteur("{$agenceEmetteur->getCodeAgence()}-{$serviceEmetteur->getCodeService()}")
            ->setAgenceDebiteur($agenceDebiteur)
            ->setServiceDebiteur($serviceDebiteur)
            ->setAgenceServiceDebiteur("{$agenceDebiteur->getCodeAgence()}-{$serviceDebiteur->getCodeService()}")
            ->setUser($this->getUser())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
        ;

        return $demandeAppro;
    }

    /** 
     * Gère l'agence et le service débiteur de la demande d'achat
     * 
     * @param DemandeIntervention $dit DIT associé à la demande d'achat
     * 
     * @return array{agence:Agence,service:Service}
     * @throws \Exception
     */
    private function handleAgenceEtServiceDebiteur(DemandeIntervention $dit): array
    {
        $agence = $service = null;
        if ($dit->getInternetExterne() === "INTERNE") {
            $agence  = $dit->getAgenceDebiteurId();
            $service = $dit->getServiceDebiteurId();
        } elseif ($dit->getInternetExterne() === "EXTERNE") {
            /** @var AtelierRealiseRepository $repository */
            $repository = $this->getEntityManager()->getRepository(AtelierRealise::class);
            $atelierRealise = $repository->findWithAgenceAndServiceByCode($dit->getReparationRealise());

            if ($atelierRealise) {
                $agence  = $atelierRealise->getAgence();
                $service = $atelierRealise->getService();
            } else {
                throw new \Exception("Atelier non trouvé pour le code: {$dit->getReparationRealise()}");
            }
        } else {
            throw new \Exception("Type de DIT non reconnu: elle n'est ni 'INTERNE' ni 'EXTERNE'");
        }

        return [
            'agence'  => $agence,
            'service' => $service,
        ];
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
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $fournisseurs = $this->daModel->getAllFournisseur($codeSociete);
        $this->fournisseurs = array_column($fournisseurs, 'numerofournisseur', 'nomfournisseur');
    }

    /**
     * Définit la date de fin souhaitée automatiquement à 3 jours ouvrables à partir d'aujourd'hui.
     *
     * @return DateTime la date de livraison prévue.
     */
    public function dateLivraisonPrevueDA(string $numDit, string $niveauUrgence): DateTime
    {
        $jours = ['P0' => 5, 'P1' => 7, 'P2' => 10, 'P3' => 15, 'P4' => 15];
        [$numOr,] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($numDit);
        $datePlanningOR = $this->getDatePlannigOr($numOr);
        if ($datePlanningOR) { // DIT avec OR plannifiée
            $dateDans12JoursOuvrables = $this->ajouterJoursOuvrables(12);
            if ($datePlanningOR < $dateDans12JoursOuvrables) { // si date planning or - date du jour < 12 (ouvrable)
                return $this->ajouterJoursOuvrables(5);
            } else { // si date planning or - date du jour >= 12 (ouvrable)
                return $this->retirerJoursOuvrables(7, $datePlanningOR); // on retire 7 jours ouvrables à la date planning or
            }
        } else { // DIT sans OR ou avec OR non plannifiée
            return $this->ajouterJoursOuvrables($jours[$niveauUrgence] ?? $jours['P4']);
        }
    }

    private function getDatePlannigOr(?string $numOr)
    {
        if (!is_null($numOr)) {
            $magasinListeOrLivrerModel = new MagasinListeOrLivrerModel();
            $data = $magasinListeOrLivrerModel->getDatePlanningPourDa($numOr);

            if (!empty($data) && !empty($data[0]['dateplanning'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $data[0]['dateplanning']);
            }
        }

        return $dateObj ?? null;
    }
}
