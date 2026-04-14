<?php

namespace App\Controller\magasin\devis;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Dto\Magasin\Devis\DevisSearchDto;
use App\Mapper\Magasin\Devis\DevisNegMapper;
use App\Model\magasin\devis\DevisNegModel;
use App\Service\ExcelService;
use App\Service\TableauEnStringService;
use Symfony\Component\Routing\Annotation\Route;

class DevisNegExportExcelController extends Controller
{
    /** 
     * @Route("/devis-neg-export-excel-list-devis-neg", name="export_excel_liste_devis_neg")
     * 
     * @return void
     */
    public function exportExcel()
    {
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
            "Relance 1",
            "Relance 2",
            "Relance 3",
            "Stop relance",
            "Position IPS",
            "PO/BC client",
            "Crée par",
            "Soumis par",
        ];

        $devisNeg = $this->getDataDevisNegEnDto();

        $data = $this->preparateDataForExcel($devisNeg, $data);

        (new ExcelService())->createSpreadsheet($data);
    }

    private function getDataDevisNegEnDto()
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        // Agences Services autorisés sur le DVM
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DVM);

        $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_neg') ?? [];

        if ($criteria instanceof DevisSearchDto) {
            $criteria = (array) $criteria;
        }

        $codeAgenceAutoriserString = TableauEnStringService::orEnString(array_column($agenceServiceAutorises, 'agence_code'));

        // Utilisation du cache de session pour la liste d'exclusion
        $session = $this->getSessionService();
        $numDeviAExclure = $session->get('devis_neg_exclure_cache');

        $listeDevisNegModel = new DevisNegModel();
        if (!$numDeviAExclure) {
            $rawExclusions = $listeDevisNegModel->getNumeroDevisExclure();
            $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $rawExclusions));
            // Si la liste est vide, on met une valeur bidon pour éviter une erreur SQL
            if (empty($numDeviAExclure)) $numDeviAExclure = "'0'";
            $session->set('devis_neg_exclure_cache', $numDeviAExclure);
        }

        $devisNeg = $listeDevisNegModel->getDevisNegExportExcel($criteria, $codeAgenceAutoriserString, $numDeviAExclure, $codeSociete);
        $devisNeg = (new DevisNegMapper())->map($devisNeg);

        return $devisNeg;
    }

    /** 
     * Convertis les données d'objet en tableau
     * 
     * @param array $dtoDevisNeg tableau d'objets à convertir
     * @param array $data tableau de retour
     * 
     * @return array
     */
    private function preparateDataForExcel(array $dtoDevisNeg, array $data): array
    {
        foreach ($dtoDevisNeg as $dtoDevis) {
            $data[] = [
                $dtoDevis->statutDw,
                $dtoDevis->statutBc,
                $dtoDevis->numeroDevis,
                $dtoDevis->dateCreation,
                $dtoDevis->emetteur,
                $dtoDevis->client,
                $dtoDevis->referenceClient,
                $dtoDevis->montantDevis,
                $dtoDevis->dateEnvoiDevisAuClient,
                $dtoDevis->statutRelance1,
                $dtoDevis->statutRelance2,
                $dtoDevis->statutRelance3,
                $dtoDevis->stopProgressionGlobal ? 'oui' : 'non',
                $dtoDevis->positionIps,
                $dtoDevis->numeroPo,
                $dtoDevis->utilisateurCreateurDevis,
                $dtoDevis->soumisPar,
            ];
        }

        return $data;
    }
}
