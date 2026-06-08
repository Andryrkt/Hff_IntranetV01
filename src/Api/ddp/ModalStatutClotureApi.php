<?php


namespace App\Api\ddp;

use App\Controller\Controller;
use App\Model\ddp\DemandePaiementModel;
use Symfony\Component\Routing\Annotation\Route;

class ModalStatutClotureApi extends Controller
{
    /**
     * @Route("/api/statut-compta/{numeroDa}/{numeroCde}", name="api_statut_compta")
     *
     * @return void
     */
    public function statutCompta(string $numeroDa, string $numeroCde)
    {
        $ddpModel = new DemandePaiementModel();
        $infoStatutClotures = $ddpModel->getInfoDdpDa($numeroDa, $numeroCde);

        $infoStatutClotures = $this->ajoutMontantTotalCommande($infoStatutClotures, $numeroCde);

        header("Content-type:application/json");
        echo json_encode($infoStatutClotures);
    }

    /**
     * Ajout du montant total de la commande dans le tableau des informations de statut de clôture.
     *
     * @param array $infoStatutClotures
     * @return array
     */
    private function ajoutMontantTotalCommande(array $infoStatutClotures, string $numeroCde): array
    {
        $ddpModel = new DemandePaiementModel();
        foreach ($infoStatutClotures as &$infoStatutCloture) {
            $montantTotalCommande = $ddpModel->getMontantCde($numeroCde, $infoStatutCloture['code_societe']);
            $mttCdeHt = $montantTotalCommande['montant_total_cde_ht']; // montant total commande Hors Taxe
            $infoStatutCloture['montant_total_commande'] = $mttCdeHt;

            $montantHtFloat = (float) str_replace([' ', "\xc2\xa0", ','], ['', '', '.'], (string) $infoStatutCloture['montant_ht']);
            $infoStatutCloture['ratio_deja_paye'] = $mttCdeHt != 0
                ? round(($montantHtFloat / $mttCdeHt) * 100, 2)
                : 0;
        }
        return $infoStatutClotures;
    }
}
