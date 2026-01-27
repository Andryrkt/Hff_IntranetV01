<?php

namespace App\Controller\Traits\da\affectation;

use DateTime;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Model\da\DaReapproModel;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeApproParentLine;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Repository\da\DaObservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\da\DemandeApproParentRepository;
use App\Repository\da\DaSoumisAValidationRepository;

trait DaAffectationTrait
{
    use DaAfficherTrait;

    //=====================================================================================
    private EntityManagerInterface $em;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproParentRepository $demandeApproParentRepository;
    private DaSoumisAValidationRepository $daSoumisAValidationRepository;
    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaAffectationTrait(): void
    {
        $this->initDaTrait();
        $this->em = $this->getEntityManager();
        $this->demandeApproParentRepository  = $this->em->getRepository(DemandeApproParent::class);
        $this->daSoumisAValidationRepository = $this->em->getRepository(DaSoumisAValidation::class);
        $this->daObservationRepository       = $this->em->getRepository(DaObservation::class);
    }
    //=====================================================================================

    /**
     * Traite les lignes d'une demande parent
     *
     * @param ArrayCollection    $daParentLines  Collection des lignes de la demande parent
     * @param DemandeApproParent $daParent       Objet de la demande parent
     * @param int                $daType         Type de la demande
     */
    private function traitementDaParentLines(ArrayCollection $daParentLines, DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = $this->createDemandeAppro($daParent, $daType);

        $numLigne = 0;
        /** @var DemandeApproParentLine $daParentLine */
        foreach ($daParentLines as $daParentLine) {
            $demandeApproLine = new DemandeApproL();

            $demandeApproLine
                ->duplicateDaParentLine($daParentLine)
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setNumeroLigne(++$numLigne)
                ->setStatutDal($demandeAppro->getStatutDal())
                ->setEstValidee($demandeAppro->getEstValidee())
                ->setValidePar($demandeAppro->getValidePar())
            ;

            // ajouter dans la collection des DAL de la nouvelle DA
            $demandeAppro->addDAL($demandeApproLine);

            $this->em->persist($demandeApproLine);
        }
        $this->em->persist($demandeAppro);
        $this->em->flush();

        $validationDA = $daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL;
        $statutDW = $validationDA ? DemandeAppro::STATUT_DW_A_VALIDE : '';

        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro(), $validationDA, $statutDW);

        if ($validationDA) $this->ajouterDansDaSoumisAValidation($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getDemandeur());
    }

    /**
     * Crée une DA à partir d'une DA parent et du type de DA
     *
     * @param DemandeApproParent $daParent Objet de la demande parent
     * @param int                $daType   Type de la demande
     *
     * @return DemandeAppro
     */
    private function createDemandeAppro(DemandeApproParent $daParent, int $daType)
    {
        $demandeAppro = new DemandeAppro();

        $prefix = [
            DemandeAppro::TYPE_DA_DIRECT           => 'DAPD',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'DAPP',
        ];

        $statut = [
            DemandeAppro::TYPE_DA_DIRECT           => DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => DemandeAppro::STATUT_VALIDE,
        ];

        $numDa = str_replace('DAP', $prefix[$daType], $daParent->getNumeroDemandeAppro());

        $demandeAppro
            ->duplicateDaParent($daParent)
            ->setDaTypeId($daType)
            ->setNumeroDemandeAppro($numDa)
            ->setStatutDal($statut[$daType])
        ;

        if ($daType === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL) {
            $demandeAppro
                ->setEstValidee(true)
                ->setValidateur($this->getUser())
                ->setValidePar($this->getUser()->getNomUtilisateur())
            ;
        }
        return $demandeAppro;
    }

    /**
     * Ajoute les données d'une Demande de Réappro dans la table `DaSoumisAValidation`
     *
     * @param string $numeroDemandeAppro  Numéro de la demande de réappro à traiter
     * @param string $demandeur           Demandeur de la demande de réappro
     */
    private function ajouterDansDaSoumisAValidation(string $numeroDemandeAppro, string $demandeur): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($numeroDemandeAppro);
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($numeroDemandeAppro)
            ->setNumeroVersion($numeroVersion)
            ->setStatut(DemandeAppro::STATUT_DW_A_VALIDE)
            ->setUtilisateur($demandeur)
        ;

        $this->em->persist($daSoumisAValidation);
        $this->em->flush();
    }


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
        $montantTotal = array_fill_keys($monthsList, 0.0); // initialiser à 0.0 tous les montants totals

        $datas = (new DaReapproModel())->getHistoriqueConsommation($dateRange, $demandeAppro);

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

            $mois = $row['mois_annee'];

            // Ajouter la quantité pour le mois correspondant
            $qte  = (float)($row['qte_fac'] ?? 0);
            $result[$key]['qteTotalTemp'] += $qte;
            $result[$key]['qteTemp'][$mois] += $qte;

            // Ajouter le montant pour le mois correspondant
            $mttTotal  = (float)($row['mtt_total'] ?? 0);
            $montantTotal[$mois] += $mttTotal;
        }

        // ✅ Formattage final
        foreach ($result as $key => $row) {
            $row['qteTotal'] = number_format($row['qteTotalTemp'], 2, ',', '');
            $row['qte'] = [];
            foreach ($monthsList as $mois) {
                $row['qte'][$mois] = $row['qteTemp'][$mois] != 0 ? number_format($row['qteTemp'][$mois], 2, ',', '') : '-';
            }
            unset($row['qteTemp'], $row['qteTotalTemp']);
            $result[$key] = $row;
        }

        // ✅ Formatage des montants
        foreach ($montantTotal as $mois => $value) {
            $montantTotal[$mois] = $value != 0 ? number_format($value, 2, ',', ' ') : '-';
        }

        return [
            'data'     => $result,
            'montants' => $montantTotal
        ];
    }
}
