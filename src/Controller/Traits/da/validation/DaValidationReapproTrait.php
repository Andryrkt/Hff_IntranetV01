<?php

namespace App\Controller\Traits\da\validation;

use DateTime;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Model\da\DaReapproModel;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DaSoumisAValidationRepository;

trait DaValidationReapproTrait
{
    use DaValidationTrait;
    private DaObservationRepository $daObservationRepository;
    private DaSoumisAValidationRepository $daSoumisAValidationRepository;
    private DaReapproModel $daReapproModel;
    private string $cheminDeBase;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationReapproTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->daSoumisAValidationRepository = $em->getRepository(DaSoumisAValidation::class);
        $this->daReapproModel = new DaReapproModel;
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
    }
    //==================================================================================================

    /**
     * Cette fonction calcule dynamiquement la période de 12 mois glissants pour un SQL BETWEEN.
     * Elle retourne :
     *   - le premier jour du mois il y a 12 mois
     *   - le dernier jour du mois précédent
     *
     * Exemple : si aujourd'hui = 28/10/2025
     *   start = 2024-10-01
     *   end   = 2025-09-30
     *
     * @return array ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD']
     */
    private function getLast12MonthsRange(): array
    {
        $startDate = new DateTime('first day of -12 months');
        $endDate = new DateTime('last day of last month');
        return [
            'start' => $startDate->format('Y-m-d'),
            'end'   => $endDate->format('Y-m-d')
        ];
    }

    /**
     * Génère une liste de tous les mois entre deux dates.
     * Chaque mois est formaté en 'MM-YYYY'.
     *
     * @param string $startDate Date de début au format 'Y-m-d' (ex: 2024-10-01)
     * @param string $endDate   Date de fin au format 'Y-m-d' (ex: 2025-09-30)
     * @return array            Tableau de mois ['10-2024','11-2024', ...]
     */
    private function getMonthsList(string $startDate, string $endDate): array
    {
        $months = [];
        $monthsLabel = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        // Convertir les chaînes en objets DateTime
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        // S'assurer que l'on prend le premier jour du mois de fin
        $end->modify('first day of this month');

        // Boucle sur chaque mois
        while ($start <= $end) {
            $month = $start->format('m-Y'); // ex: 10-2024
            [$mois, $annee] = explode('-', $month);
            $months[] = $monthsLabel[$mois - 1]  . '-' . $annee;
            $start->modify('+1 month');
        }

        return $months;
    }

    public function getHistoriqueConsommation(DemandeAppro $demandeAppro, array $dateRange, array $monthsList)
    {
        $result = [];
        $codeAgence = $demandeAppro->getAgenceEmetteur()->getCodeAgence();
        $codeService = $demandeAppro->getServiceEmetteur()->getCodeService();

        $datas = $this->daReapproModel->getHistoriqueConsommation($dateRange, $codeAgence, $codeService);

        foreach ($datas as $row) {
            // Clé unique par produit
            $key = md5("{$row['cst']}|{$row['refp']}|{$row['desi']}");

            // Initialiser si pas déjà existant
            if (!isset($result[$key])) {
                $result[$key] = [
                    'cst'          => $row['cst'],
                    'refp'         => $row['refp'],
                    'desi'         => $row['desi'],
                    'qteTotalTemp' => 0.0,
                    'qteTemp'      => array_fill_keys($monthsList, 0.0),
                ];
            }

            // Ajouter la quantité pour le mois correspondant
            $mois = $row['mois_annee'];
            $qte  = (float)($row['qte_fac'] ?? 0);
            $result[$key]['qteTotalTemp'] += $qte;
            $result[$key]['qteTemp'][$mois] += $qte;
        }

        // Formattage final
        foreach ($result as $key => $row) {
            $row['qteTotal'] = number_format($row['qteTotalTemp'], 2, ',', '');
            $row['qte'] = [];
            foreach ($monthsList as $mois) {
                $row['qte'][$mois] = $row['qteTemp'][$mois] > 0 ? number_format($row['qteTemp'][$mois], 2, ',', '') : '-';
            }
            unset($row['qteTemp'], $row['qteTotalTemp']);
            $result[$key] = $row;
        }

        return $result;
    }

    private function modifierStatut(DemandeAppro $demandeAppro, string $statut)
    {
        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            $this->getEntityManager()->persist($demandeApproL);
        }

        $demandeAppro->setStatutDal($statut);
        $this->getEntityManager()->persist($demandeAppro);
        $this->getEntityManager()->flush();
    }

    /**
     * Ajoute les données d'une Demande de Réappro dans la table `DaSoumisAValidation`
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande de réappro à traiter
     */
    private function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion($numeroVersion)
            ->setStatut(DemandeAppro::STATUT_DW_A_VALIDE)
            ->setDateSoumission(new DateTime())
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
        $this->getEntityManager()->flush();
    }

    public function validerDemande(DemandeAppro $demandeAppro)
    {
        $this->modifierStatut($demandeAppro, demandeAppro::STATUT_VALIDE);
        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro(), true, DemandeAppro::STATUT_DW_A_VALIDE);
    }

    public function refuserDemande(DemandeAppro $demandeAppro)
    {
        $this->modifierStatut($demandeAppro, demandeAppro::STATUT_REFUSE_APPRO);
        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro());
    }
}
