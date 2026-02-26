<?php

namespace App\Controller\magasin\devis;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Service\ExcelService;
use App\Service\TableauEnStringService;

class DevisMagasinExportExcelController extends Controller
{
    private ListeDevisMagasinModel $listeDevisMagasinModel;
    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
    }

    /**
     * @Route("/devis-magasin-export-excel-list-devis-magasin", name="devis_magasin_export_excel_list_devis_magasin")
     *
     * @return void
     */
    public function exportExcel()
    {
        $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');

        if ($criteria && $criteria["emetteur"]) {
            $criteria["emetteur"]["agence"] = $criteria["emetteur"]["agence"] ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria["emetteur"]["agence"]) : null;
            $criteria["emetteur"]["service"] = $criteria["emetteur"]["service"] ? $this->getEntityManager()->getRepository(Service::class)->find($criteria["emetteur"]["service"]) : null;
        }

        $listeDevisFactory = $this->recuperationDonner($criteria);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "Statut devis",
            "Statut BC",
            "Numéro devis",
            "Date de création",
            "Emetteur",
            "Client",
            "Libellé",
            "Montant",
            "Date d'envoi devis au client",
            "Statut relance",
            "relancé le",
            "Position IPS",
            "Crée par",
            "Soumis par",
        ];

        $data = $this->convertirObjetEnTableau($listeDevisFactory, $data);

        (new ExcelService())->createSpreadsheet($data);
    }

    /** 
     * Convertis les données d'objet en tableau
     * 
     * @param array $listeDevisFactory tableau d'objets à convertir
     * @param array $data tableau de retour
     * 
     * @return array
     */
    private function convertirObjetEnTableau(array $listeDevisFactory, array $data): array
    {
        /** @var ListeDevisMagasinFactory $devisFactory */
        foreach ($listeDevisFactory as $devisFactory) {
            $data[] = [
                $devisFactory->getStatutDw() ? $devisFactory->getStatutDw() : "A traiter",
                $devisFactory->getStatutBc(),
                $devisFactory->getNumeroDevis(),
                $devisFactory->getDateCreation(),
                $devisFactory->getSuccursaleServiceEmetteur(),
                $devisFactory->getCodeClientLibelleClient(),
                $devisFactory->getReferenceCLient(),
                $devisFactory->getMontant(),
                $devisFactory->getDateDenvoiDevisAuClient(),
                $devisFactory->getStatutRelance(),
                $devisFactory->getDateDerniereRelance() . ' - ' . $devisFactory->getNombreDeRelance(),
                $devisFactory->getStatutIps(),
                $devisFactory->getCreePar(),
                $devisFactory->getOperateur(),
            ];
        }

        return $data;
    }

    public function recuperationDonner(array $criteria = []): array
    {
        $codeAgenceAutoriserString = TableauEnStringService::orEnString($this->getUser()->getAgenceAutoriserCode());
        $vignette = 'magasin';
        $adminMutli          = in_array(1, $this->getUser()->getRoleIds()) || in_array(6, $this->getUser()->getRoleIds());

        $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $this->listeDevisMagasinModel->getNumeroDevisExclure()));

        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure);

        $listeDevisFactory = [];
        $dejaVu = []; // Tableau pour mémoriser les numéros de devis déjà traités

        foreach ($devisIps as $devisIp) {
            $numeroDevis = $devisIp['numero_devis'] ?? null;

            // Si on a déjà traité ce numéro de devis → on ignore
            if ($numeroDevis === null || in_array($numeroDevis, $dejaVu, true)) {
                continue;
            }

            $dejaVu[] = $numeroDevis; // On le marque comme vu

            $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

            // Récupération de la version maximale
            $numeroVersionMax = $devisMagasinRepository->getNumeroVersionMax($numeroDevis);
            $devisSoumi       = $devisMagasinRepository->findOneBy([
                'numeroDevis'    => $numeroDevis,
                'numeroVersion'  => $numeroVersionMax
            ]);

            // Ajout des informations complémentaires
            $devisIp['statut_dw']                  = $devisSoumi ? $devisSoumi->getStatutDw()                  : DevisMagasin::STATUT_A_TRAITER;
            $devisIp['operateur']                  = $devisSoumi ? $devisSoumi->getUtilisateur()               : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel
                ->getUtilisateurCreateurDevis($numeroDevis) ?? '';
            $devisIp['statut_bc']                  = $devisSoumi ? $devisSoumi->getStatutBc()                  : '';

            // statut DW = A traiter et statut BC = TR
            if ($devisIp['statut_dw'] === DevisMagasin::STATUT_A_TRAITER && $devisIp['statut_ips'] === 'TR') {
                continue;
            }

            // Application des filtres critères
            if (!empty($criteria) && !$this->matchesCriteria($devisIp, $criteria)) {
                continue;
            }

            // Transformation via le factory
            $listeDevisFactory[] = (new ListeDevisMagasinFactory())->transformationEnObjet($devisIp);
        }

        return $listeDevisFactory;
    }

    private function matchesCriteria(array $devisIp, array $criteria): bool
    {
        // Filtre par numéro de devis
        if (
            !empty($criteria['numeroDevis']) &&
            stripos($devisIp['numero_devis'], $criteria['numeroDevis']) === false
        ) {
            return false;
        }

        // Filtre par code client
        if (
            !empty($criteria['codeClient']) &&
            stripos($devisIp['client'] ?? '', $criteria['codeClient']) === false
        ) {
            return false;
        }

        // Filtre par opérateur (utilisateur qui a soumis le devis)
        if (
            !empty($criteria['Operateur']) &&
            stripos($devisIp['operateur'] ?? '', $criteria['Operateur']) === false
        ) {
            return false;
        }

        // Filtre par utilisateur createur
        if (
            !empty($criteria['creePar']) &&
            stripos($devisIp['utilisateur_createur_devis'] ?? '', $criteria['creePar']) === false
        ) {
            return false;
        }

        // Filtre par statut DW
        if (
            !empty($criteria['statutDw']) &&
            $devisIp['statut_dw'] !== $criteria['statutDw']
        ) {
            return false;
        }

        // Filtre par statut IPS
        if (
            !empty($criteria['statutIps']) &&
            $devisIp['statut_ips'] !== $criteria['statutIps']
        ) {
            return false;
        }

        //Filtre par statut BC
        if (
            !empty($criteria['statutBc']) &&
            $devisIp['statut_bc'] !== $criteria['statutBc']
        ) {
            return false;
        }

        // Filtre par agence émetteur
        if (!empty($criteria['emetteur']['agence'])) {
            // Récupérer les 2 premiers caractères de l'agence émetteur
            $agenceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], 0, 2) : '';
            if ($agenceEmetteurCode !== $criteria['emetteur']['agence']->getCodeAgence()) {
                return false;
            }
        }

        // Filtre par service émetteur
        if (!empty($criteria['emetteur']['service'])) {
            // Récupérer les 3 derniers caractères du service émetteur
            $serviceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], -3) : '';
            if ($serviceEmetteurCode !== $criteria['emetteur']['service']->getCodeService()) {
                return false;
            }
        }

        // Filtre par date de création (début)
        if (!empty($criteria['dateCreation']['debut'])) {
            try {
                $dateCreation = new \DateTime($devisIp['date_creation']);
                $dateDebut = $criteria['dateCreation']['debut'];
                // Comparer seulement la partie date (sans l'heure)
                if ($dateCreation->format('Y-m-d') < $dateDebut->format('Y-m-d')) {
                    return false;
                }
            } catch (\Exception $e) {
                // Si la date n'est pas valide, ignorer ce filtre
                return true;
            }
        }

        // Filtre par date de création (fin)
        if (!empty($criteria['dateCreation']['fin'])) {
            try {
                $dateCreation = new \DateTime($devisIp['date_creation']);
                $dateFin = $criteria['dateCreation']['fin'];
                // Comparer seulement la partie date (sans l'heure)
                if ($dateCreation->format('Y-m-d') > $dateFin->format('Y-m-d')) {
                    return false;
                }
            } catch (\Exception $e) {
                // Si la date n'est pas valide, ignorer ce filtre
                return true;
            }
        }

        return true;
    }
}
