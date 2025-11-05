<?php

namespace App\Controller\da\reappro;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\reappro\ReportingIpsTrait;

/**
 * @Route("/demande-appro")
 */
class ExportExcelController extends Controller
{
    use ReportingIpsTrait;

    /**
     * @Route("/reappro-export-excel", name = "reappro_export_excel")
     */
    public function exportExcel()
    {
        $this->verifierSessionUtilisateur();

        $criterias = $this->getSessionService()->get('criterias_reporting_ips');

        $reportingIpsData = $this->getData($criterias);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "Agence débiteur",
            "Service débiteur",
            "Date commande",
            "Numéro facture",
            "Référence Client",
            "Constructeur",
            "Référence produit",
            "Désignation produit",
            "Quantité demandée",
            "Prix unitaire réel",
            "Montant"
        ];

        $data = $this->convertirObjetEnTableau($reportingIpsData, $data);

        // Ajout d'une ligne vide pour la mise en forme
        $data[] = [];

        // Préparation de la ligne des totaux
        $totalRow = array_fill(0, 11, ''); // Crée une ligne avec 11 cellules vides
        $totalRow[7] = 'Total'; // Ajoute le libellé dans la 8ème colonne
        $totalRow[8] = $reportingIpsData['qteTotale']; // Ajoute la quantité totale dans la 9ème colonne
        $totalRow[10] = number_format($reportingIpsData['montantTotal'], 2, ',', ' '); // Ajoute le montant total formaté dans la 11ème colonne
        $data[] = $totalRow;

        $this->getExcelService()->createSpreadsheet($data);
    }

    private function convertirObjetEnTableau(array $reportingIpsData, array $data): array
    {
        foreach ($reportingIpsData['reportingIps'] as $item) {
            $dateCommande = $item['date_commande'];
            // S'assurer que nous avons un objet DateTime avant de formater
            if (!$dateCommande instanceof \DateTimeInterface) {
                try {
                    $dateCommande = new \DateTime($dateCommande);
                } catch (\Exception $e) {
                    // En cas d'échec de conversion, on passe la valeur brute pour éviter un crash
                    $dateCommande = $item['date_commande'];
                }
            }

            $data[] = [
                $item['agence_debiteur'],
                $item['service_debiteur'],
                ($dateCommande instanceof \DateTimeInterface) ? $dateCommande->format('Y-m-d') : $dateCommande,
                $item['numero_facture'],
                $item['client'],
                $item['constructeur'],
                $item['reference_produit'],
                $item['designation_produit'],
                (int)$item['qte_demande'],
                (float)$item['prix_unitaire_reel'],
                (float)$item['montant']
            ];
        }
        return $data;
    }
}
