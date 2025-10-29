<?php

namespace App\Controller\da\ListeDa;

use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Repository\admin\AgenceRepository;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class ExportExcelController extends Controller
{
    private DaAfficherRepository $daAfficherRepository;
    private AgenceRepository $agenceRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->agenceRepository = $this->getEntityManager()->getRepository(Agence::class);
    }

    /** 
     * @Route("/export-excel/list-DA", name="da_export_excel_list_da")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('criteria_search_list_da');

        // recupération des données de la DA
        $dasFiltered = $this->getDataExcel($criteria);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "N° Demande",
            "Achat direct",
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
        $this->getExcelService()->createSpreadsheet($data, "donnees_" . date('YmdHis'));
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
        /** @var DaAfficher */
        foreach ($dasFiltered as $da) {
            $data[] = [
                $da->getNumeroDemandeAppro(),
                $da->getDaTypeId() == DemandeAppro::TYPE_DA_DIRECT ? 'OUI' : 'NON',
                $da->getNumeroDemandeDit(),
                $da->getNiveauUrgence(),
                $da->getNumeroOR() ?? '-',
                $da->getDemandeur(),
                $da->getDateCreation()->format('d/m/Y'),
                $da->getStatutDal(),
                $da->getStatutOr() ?? '-',
                $da->getStatutCde(),
                $da->getDatePlannigOr(),
                $da->getNomFournisseur(),
                $da->getArtRefp(),
                $da->getArtDesi(),
                $da->getEstFicheTechnique() ? 'OUI' : 'NON',
                $da->getQteDem(),
                $da->getQteEnAttent() == 0 ? '-' : $da->getQteEnAttent(),
                $da->getQteDispo() == 0 ? '-' : $da->getQteDispo(),
                $da->getQteLivrer() == 0 ? '-' : $da->getQteLivrer(),
                $da->getDateFinSouhaite()->format('d/m/Y'),
                $da->getDateLivraisonPrevue() == null ? '' : $da->getDateLivraisonPrevue()->format('d/m/Y'),
                $da->getJoursDispo()
            ];
        }

        return $data;
    }

    public function getDataExcel(array $criteria): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $userConnecter = $this->getUser();
        $codeAgence = $userConnecter->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findOneBy(['codeAgence' => $codeAgence])->getId();

        // Filtrage des DA en fonction des critères
        $daAffichers = $this->daAfficherRepository->findDerniereVersionDesDA($userConnecter, $criteria, $idAgenceUser, $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->estAdmin());

        // Retourne les DA filtrées
        return $daAffichers;
    }
}
