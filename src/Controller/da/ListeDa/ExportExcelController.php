<?php

namespace App\Controller\da\ListeDa;

use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class ExportExcelController extends Controller
{
    private EntityRepository $daAfficherRepository;
    private EntityRepository $agenceRepository;

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

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence           = $agenceServiceIps['agenceIps'];
        $codeCentrale     = $this->estAdmin() || in_array($agence->getCodeAgence(), ['90', '91', '92']);

        // recupération des données de la DA
        $dasFiltered = $this->getDataExcel($criteria);

        // Données généré des $dasFiltered
        $data = $this->generateTableData($dasFiltered, $codeCentrale);

        // Crée le fichier Excel
        $this->getExcelService()->createSpreadsheet($data, "donnees_" . date('Y-m-d_H-i-s'));
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

    /** 
     * Construis l'entête du tableau excel
     * 
     * @param bool $codeCentrale afficher le centrale ou non
     * 
     * @return array
     */
    private function headerExcel(bool $codeCentrale): array
    {
        $columnsWithCondition = [
            "N° Demande"               => true,
            "Type de demande"          => true,
            "N° DIT"                   => true,
            "Niveau urgence DIT"       => true,
            "N° OR"                    => true,
            "Demandeur"                => true,
            "Date de demande"          => true,
            "Statut DA"                => true,
            "Statut OR"                => true,
            "Statut BC"                => true,
            "Centrale"                 => $codeCentrale,
            "Date Planning OR"         => true,
            "Fournisseur"              => true,
            "CST"                      => true,
            "Réference"                => true,
            "Désignation"              => true,
            "Fiche technique"          => true,
            "Qté dem"                  => true,
            "Qté en attente"           => true,
            "Qté Dispo (Qté à livrer)" => true,
            "Qté livrée"               => true,
            "Date fin souhaitée"       => true,
            "Date livraison prévue"    => true,
            "Nbr Jour(s) dispo"        => true,
        ];

        return array_keys(array_filter($columnsWithCondition));
    }

    /** 
     * Construis le corps du tableau excel
     * 
     * @param DaAfficher[] $dasFiltered  tableau d'objets DaAfficher à convertir
     * @param array        $headers      entête du tableau
     * 
     * @return array
     */
    private function bodyExcel(array $dasFiltered, array $headers): array
    {
        $data = [];

        $typeDemande = [
            DemandeAppro::TYPE_DA_AVEC_DIT => 'DA AVEC DIT',
            DemandeAppro::TYPE_DA_DIRECT   => 'DA DIRECT',
            DemandeAppro::TYPE_DA_REAPPRO  => 'DA REAPPRO',
        ];

        // Map de chaque entête vers la valeur correspondante
        $columnCallbacks = [
            "N° Demande"               => fn(DaAfficher $da) => $da->getNumeroDemandeAppro(),
            "Type de demande"          => fn(DaAfficher $da) => $typeDemande[$da->getDaTypeId()],
            "N° DIT"                   => fn(DaAfficher $da) => $da->getNumeroDemandeDit() ?? '-',
            "Niveau urgence DIT"       => fn(DaAfficher $da) => $da->getNiveauUrgence() ?? '-',
            "N° OR"                    => fn(DaAfficher $da) => $da->getNumeroOR() ?? '-',
            "Demandeur"                => fn(DaAfficher $da) => $da->getDemandeur(),
            "Date de demande"          => fn(DaAfficher $da) => $da->getDateCreation()->format('d/m/Y'),
            "Statut DA"                => fn(DaAfficher $da) => $da->getStatutDal(),
            "Statut OR"                => fn(DaAfficher $da) => $da->getStatutOr() ?? '-',
            "Statut BC"                => fn(DaAfficher $da) => $da->getStatutCde() ?? '-',
            "Centrale"                 => fn(DaAfficher $da) => $da->getDesiCentrale() ?? '-',
            "Date Planning OR"         => fn(DaAfficher $da) => $da->getDatePlannigOr() ?? '-',
            "Fournisseur"              => fn(DaAfficher $da) => $da->getNomFournisseur() ?? '-',
            "CST"                      => fn(DaAfficher $da) => $da->getArtConstp() ?? '-',
            "Réference"                => fn(DaAfficher $da) => $da->getArtRefp(),
            "Désignation"              => fn(DaAfficher $da) => $da->getArtDesi(),
            "Fiche technique"          => fn(DaAfficher $da) => $da->getEstFicheTechnique() ? 'OUI' : 'NON',
            "Qté dem"                  => fn(DaAfficher $da) => $da->getQteDem(),
            "Qté en attente"           => fn(DaAfficher $da) => $da->getQteEnAttent() == 0 ? '-' : $da->getQteEnAttent(),
            "Qté Dispo (Qté à livrer)" => fn(DaAfficher $da) => $da->getQteDispo() == 0 ? '-' : $da->getQteDispo(),
            "Qté livrée"               => fn(DaAfficher $da) => $da->getQteLivrer() == 0 ? '-' : $da->getQteLivrer(),
            "Date fin souhaitée"       => fn(DaAfficher $da) => $da->getDateFinSouhaite()->format('d/m/Y'),
            "Date livraison prévue"    => fn(DaAfficher $da) => $da->getDateLivraisonPrevue() ? $da->getDateLivraisonPrevue()->format('d/m/Y') : '',
            "Nbr Jour(s) dispo"        => fn(DaAfficher $da) => $da->getJoursDispo(),
        ];

        /** @var DaAfficher[] $dasFiltered */
        foreach ($dasFiltered as $da) {
            $row = [];
            foreach ($headers as $col) {
                $row[] = $columnCallbacks[$col]($da);
            }
            $data[] = $row;
        }
        return $data;
    }

    /** 
     * Générer la table complète (entête + corps) pour l'Excel
     * 
     * @param DaAfficher[] $dasFiltered  tableau d'objets DaAfficher à convertir
     * @param bool         $codeCentrale afficher le centrale ou non
     * 
     * @return array
     */
    private function generateTableData(array $dasFiltered, bool $codeCentrale): array
    {
        $headers = $this->headerExcel($codeCentrale);

        $body = $this->bodyExcel($dasFiltered, $headers);

        return array_merge([$headers], $body);
    }
}
