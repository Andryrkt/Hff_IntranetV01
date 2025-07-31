<?php

namespace App\Controller\da\ListeDa;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ExportExcelController extends Controller
{
    /** 
     * @Route("/export-excel/list-DA", name="da_export_excel_list_da")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->sessionService->get('criteria_for_excel');

        //recuperation de l'id de l'agence de l'utilisateur connecter
        $codeAgence = Controller::getUser()->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findOneBy(['codeAgence' => $codeAgence])->getId();
        // recupération des données de la DA
        $dasFiltered = $this->donnerAfficher($criteria, $idAgenceUser);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "N° Demande",
            "N° DIT",
            "Niveau urgence DIT",
            "N° OR",
            "Demandeur",
            "Date de demande",
            "Statut DA",
            "Statut OR",
            "Statut BC",
            "Date Planning OR",
            "Fournisseur",
            "Réference",
            "Désignation",
            "Fiche technique",
            "Qté dem",
            "Qté en attente",
            "Qté Dispo (Qté à livrer)",
            "Qté livrée",
            "Date fin souhaitée",
            "Date livraison prévue",
            "Nbr Jour(s) dispo"
        ];

        // Convertir les entités en tableau de données
        $data = $this->convertirObjetEnTableau($dasFiltered, $data);

        // Crée le fichier Excel
        $this->excelService->createSpreadsheet($data, "donnees_" . date('YmdHis'));
    }




    /** 
     * Convertis les données d'objet en tableau
     * 
     * @param array $dasFiltered tableau d'objets à convertir
     * @param array $data tableau de retour
     * 
     * @return array
     */
    private function convertirObjetEnTableau(array $dasFiltered, array $data): array
    {
        /** @var DemandeAppro $da chaque DA dans $dasFiltered */
        foreach ($dasFiltered as $da) {
            /** @var DemandeApproL|DemandeApproLR $davp DAL ou DALR */
            foreach ($da->getDaValiderOuProposer() as $davp) {
                $data[] = [
                    $da->getNumeroDemandeAppro(),
                    $da->getNumeroDemandeDit(),
                    $da->getDit()->getIdNiveauUrgence()->getDescription(),
                    $da->getDit()->getNumeroOR() ?? '-',
                    $da->getDemandeur(),
                    $da->getDateCreation()->format('d/m/Y'),
                    $davp->getStatutDal(),
                    $da->getDit()->getStatutOr() ?? '-',
                    $davp->getStatutBc(),
                    $davp->getDatePlanningOR(),
                    $davp->getNomFournisseur(),
                    $davp->getArtRefp(),
                    $davp->getArtDesi(),
                    $davp->getEstFicheTechnique() ? 'OUI' : 'NON',
                    $davp->getQteDem(),
                    $davp->getQteEnAttent() == 0 ? '-' : $davp->getQteEnAttent(),
                    $davp->getQteDispo() == 0 ? '-' : $davp->getQteDispo(),
                    $davp->getQteLivee() == 0 ? '-' : $davp->getQteLivee(),
                    $davp->getDateFinSouhaite()->format('d/m/Y'),
                    $davp->getDateLivraisonPrevue() == null ? '' : $davp->getDateLivraisonPrevue()->format('d/m/Y'),
                    $davp->getJoursDispo()
                ];
            }
        }

        return $data;
    }
}
