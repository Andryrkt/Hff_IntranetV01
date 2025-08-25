<?php

namespace App\Controller\Traits\da;

use App\Model\da\DaModel;
use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Repository\admin\AgenceRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\da\DaHistoriqueDemandeModifDARepository;
use Twig\Markup;

trait DaListeTrait
{
    use DaTrait;

    //=====================================================================================
    private $urlGenerator;

    // Styles des DA, OR, BC dans le css
    private $styleStatutDA = [];
    private $styleStatutOR = [];
    private $styleStatutBC = [];

    // Repository et model
    private DaModel $daModel;
    private DossierInterventionAtelierModel $dwModel;
    private AgenceRepository $agenceRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DaHistoriqueDemandeModifDARepository $historiqueModifDARepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaListeTrait($generator)
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->urlGenerator = $generator;

        //----------------------------------------------------------------------------------------------------
        $this->styleStatutDA = [
            DemandeAppro::STATUT_VALIDE              => 'bg-bon-achat-valide fw-bold',
            DemandeAppro::STATUT_TERMINER            => 'bg-primary text-white fw-bold',
            DemandeAppro::STATUT_SOUMIS_ATE          => 'bg-proposition-achat fw-bold',
            DemandeAppro::STATUT_SOUMIS_APPRO        => 'bg-demande-achat fw-bold',
            DemandeAppro::STATUT_A_VALIDE_DW         => 'bg-warning text-secondary',
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE => 'bg-creation-demande-initiale fw-bold',
        ];
        $this->styleStatutOR = [
            DitOrsSoumisAValidation::STATUT_VALIDE                     => 'bg-danger text-white',
            'Refusé chef atelier'                                      => 'bg-or-non-valide',
            'Refusé client interne'                                    => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION => 'bg-warning text-secondary',
        ];
        $this->styleStatutBC = [
            'A générer'                => 'bg-bc-a-generer fw-bold',
            'A éditer'                 => 'bg-bc-a-editer fw-bold',
            'A soumettre à validation' => 'bg-bc-a-soumettre-a-validation fw-bold',
            'Validé'                   => 'bg-bc-valide fw-bold',
            'Cloturé'                  => 'bg-bc-valide fw-bold',
            'Non validé'               => 'bg-bc-non-valide fw-bold',
            'Tous livrés'              => 'tout-livre bg-success text-white',
            'Partiellement livré'      => 'partiellement-livre bg-warning text-white',
            'Partiellement dispo'      => 'partiellement-dispo bg-info text-white',
            'Complet non livré'        => 'complet-non-livre bg-primary text-white',
        ];
        //----------------------------------------------------------------------------------------------------
        $this->daModel = new DaModel();
        $this->dwModel = new DossierInterventionAtelierModel();
        $this->agenceRepository = $em->getRepository(Agence::class);
        $this->daSoumissionBcRepository = $em->getRepository(DaSoumissionBc::class);
        $this->historiqueModifDARepository = $em->getRepository(DaHistoriqueDemandeModifDA::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        //----------------------------------------------------------------------------------------------------
    }
    //=====================================================================================

    /**
     * Met à jour le champ `joursDispo` pour chaque DAL sauf si elle est déjà validée.
     *
     * @param iterable<DemandeApproL> $dalDernieresVersions
     */
    private function ajoutNbrJourRestant($dalDernieresVersions)
    {
        foreach ($dalDernieresVersions as $dal) {
            if ($dal->getStatutDal() != DemandeAppro::STATUT_VALIDE) { // si le statut de la DAL est différent de "Bon d’achats validé" 
                $dal->setJoursDispo($this->getJoursRestants($dal));
            }
        }
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     *  @param DaAfficher[] $data données avant préparation
     *  @param array $numDaNonDeverrouillees
     **/
    private function prepareDataForDisplay(array $data, array $numDaNonDeverrouillees): array
    {
        $datasPrepared = [];

        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        foreach ($data as $item) {
            // Pré-calculer les styles
            $styleStatutDA = $this->styleStatutDA[$item->getStatutDal()] ?? '';
            $styleStatutOR = $this->styleStatutOR[$item->getStatutOr()] ?? '';
            $styleStatutBC = $this->styleStatutBC[$item->getStatutCde()] ?? '';

            // Pré-calculer les booléens
            $ajouterDA = false && !$item->getAchatDirect() && ($this->estUserDansServiceAtelier() || $this->estAdmin()); // pas achat direct && (atelier ou admin)  
            $aDeverouiller = $this->estUserDansServiceAppro() && in_array($item->getNumeroDemandeAppro(), $numDaNonDeverrouillees);
            $demandeDeverouiller = $this->estUserDansServiceAtelier() && $item->getDemandeDeverouillage();
            $supprimable = ($this->estUserDansServiceAppro() && $item->getStatutDal() === DemandeAppro::STATUT_SOUMIS_APPRO) || ($this->estUserDansServiceAtelier() && $item->getStatutDal() === DemandeAppro::STATUT_SOUMIS_ATE);
            $statutOrValide = $item->getStatutOr() === DitOrsSoumisAValidation::STATUT_VALIDE;
            $pathOrMax = $this->dwModel->findCheminOrVersionMax($item->getNumeroOr());
            $telechargerOR = $statutOrValide && !empty($pathOrMax);

            // Pré-calculer les URLs
            $urlDetail = $this->urlGenerator->generate(
                $item->getAchatDirect() ? 'da_detail_direct' : 'da_detail_avec_dit',
                ['id' => $item->getDemandeAppro()->getId()]
            );
            $urlProposition = $this->urlGenerator->generate(
                $item->getAchatDirect() ? 'da_proposition_direct' : 'da_proposition_ref_avec_dit',
                ['id' => $item->getDemandeAppro()->getId()]
            );
            $urlDelete = $this->urlGenerator->generate(
                $item->getAchatDirect() ? 'da_delete_line_direct' : 'da_delete_line_avec_dit',
                ['numDa' => $item->getNumeroDemandeAppro(), 'ligne' => $item->getNumeroLigne()]
            );

            $achatDirect = $item->getAchatDirect();

            // formatter l'item (DaAfficher) à afficher
            $formattedItem = [
                'dit'                 => $item->getDit(),
                'numeroDemandeAppro'  => $item->getNumeroDemandeAppro(),
                'demandeAppro'        => $item->getDemandeAppro(),
                'achatDirect'         => $achatDirect ? $safeIconSuccess : '',
                'numeroDemandeDit'    => $item->getNumeroDemandeDit() ?? $safeIconBan,
                'numeroOr'            => $achatDirect ? $safeIconBan : $item->getNumeroOr(),
                'niveauUrgence'       => $item->getNiveauUrgence(),
                'demandeur'           => $item->getDemandeur(),
                'dateDemande'         => $item->getDateDemande() ? $item->getDateDemande()->format('d/m/Y') : '',
                'statutDal'           => $item->getStatutDal(),
                'statutOr'            => $achatDirect ? $safeIconBan : $item->getStatutOr(),
                'statutCde'           => $item->getStatutCde(),
                'datePlannigOr'       => $achatDirect ? $safeIconBan : ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : ''),
                'nomFournisseur'      => $item->getNomFournisseur(),
                'artRefp'             => $item->getArtRefp(),
                'artDesi'             => $item->getArtDesi(),
                'estDalr'             => $item->getEstDalr(),
                'verouille'           => $item->getVerouille(),
                'numeroligne'         => $item->getNumeroLigne(),
                'estFicheTechnique'   => $item->getEstFicheTechnique() ? $safeIconSuccess : $safeIconXmark,
                'qteDem'              => $item->getQteDem() == 0 ? '-' : $item->getQteDem(),
                'qteEnAttent'         => $item->getQteEnAttent() == 0 ? '-' : $item->getQteEnAttent(),
                'qteDispo'            => $item->getQteDispo() == 0 ? '-' : $item->getQteDispo(),
                'qteLivrer'           => $item->getQteLivrer() == 0 ? '-' : $item->getQteLivrer(),
                'dateFinSouhaite'     => $item->getDateFinSouhaite() ? $item->getDateFinSouhaite()->format('d/m/Y') : '',
                'dateLivraisonPrevue' => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('d/m/Y') : '',
                'joursDispo'          => $item->getJoursDispo(),
            ];

            // Tout regrouper
            $datasPrepared[] = [
                'item'                => $formattedItem,
                'styleStatutDA'       => $styleStatutDA,
                'styleStatutOR'       => $styleStatutOR,
                'styleStatutBC'       => $styleStatutBC,
                'aDeverouiller'       => $aDeverouiller,
                'urlDetail'           => $urlDetail,
                'urlProposition'      => $urlProposition,
                'urlDelete'           => $urlDelete,
                'ajouterDA'           => $ajouterDA,
                'supprimable'         => $supprimable,
                'telechargerOR'       => $telechargerOR,
                'pathOrMax'           => $pathOrMax,
                'statutValide'        => $item->getStatutDal() === DemandeAppro::STATUT_VALIDE,
                'demandeDeverouiller' => $demandeDeverouiller,
            ];
        }

        // résultat avec tri des données
        /* usort(
            $datasPrepared,
            function ($a, $b) {
                return $a['item']['joursDispo'] <=> $b['item']['joursDispo']; // tri croissant
            }
        ); */
        return $datasPrepared;
    }
}
