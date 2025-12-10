<?php

namespace App\Controller\pol\devis;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Service\TableauEnStringService;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;

/**
 * @Route("/pol")
 */
class DevisMagasinPolExportExcelController extends Controller
{
    private ListeDevisMagasinModel $listeDevisMagasinModel;
    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
    }

    /**
     * @Route("/devis-magasin-pol-export-excel", name="devis_magasin_pol_export_excel")
     *
     * @return void
     */
    public function exportExcel()
    {
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin_pol');
        $criteria["emetteur"]["agence"] = $this->getEntityManager()->getRepository(Agence::class)->find($criteria["emetteur"]["agence"]);
        $criteria["emetteur"]["service"] = $this->getEntityManager()->getRepository(Service::class)->find($criteria["emetteur"]["service"]);

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
            "Position IPS",
            "Crée par",
            "Soumis par",
        ];

        $data = $this->convertirObjetEnTableau($listeDevisFactory, $data);

        $this->getExcelService()->createSpreadsheet($data);
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
                $devisFactory->getStatutDw(),
                $devisFactory->getStatutBc(),
                $devisFactory->getNumeroDevis(),
                $devisFactory->getDateCreation(),
                $devisFactory->getSuccursaleServiceEmetteur(),
                $devisFactory->getCodeClientLibelleClient(),
                $devisFactory->getReferenceCLient(),
                $devisFactory->getMontant(),
                $devisFactory->getDateDenvoiDevisAuClient(),
                $devisFactory->getStatutIps(),
                $devisFactory->getCreePar(),
                $devisFactory->getOperateur(),
            ];
        }

        return $data;
    }

    public function recuperationDonner(array $criteria = []): array
    {
        // recupération de la liste des devis magasin dans IPS
        $codeAgenceAutoriserString = TableauEnStringService::orEnString($this->getUser()->getAgenceAutoriserCode());
        $vignette = 'pneumatique';
        $adminMutli          = in_array(1, $this->getUser()->getRoleIds()) || in_array(6, $this->getUser()->getRoleIds());
        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli);

        $listeDevisFactory = [];
        foreach ($devisIps as  $devisIp) {
            $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
            //recupération des information de devis soumission à validation neg
            $numeroVersionMax = $devisMagasinRepository->getNumeroVersionMax($devisIp['numero_devis']);
            $devisSoumi = $devisMagasinRepository->findOneBy(['numeroDevis' => $devisIp['numero_devis'], 'numeroVersion' => $numeroVersionMax]);
            //ajout des informations manquantes
            $devisIp['statut_dw'] = $devisSoumi ? $devisSoumi->getStatutDw() : '';
            $devisIp['operateur'] = $devisSoumi ? $devisSoumi->getUtilisateur() : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel->getUtilisateurCreateurDevis($devisIp['numero_devis']) ?? '';
            $devisIp['statut_bc'] = $devisSoumi ? $devisSoumi->getStatutBc() : '';

            // Appliquer les filtres si des critères sont fournis
            if (!empty($criteria) && !$this->matchesCriteria($devisIp, $criteria)) {
                continue; // Ignorer cet élément s'il ne correspond pas aux critères
            }

            //transformation par le factory
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
