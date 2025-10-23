<?php

namespace App\Controller\Traits\da;

use App\Model\da\DaModel;
use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSearch;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Repository\admin\AgenceRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Service\Users\UserDataService;
use Twig\Markup;

trait DaListeTrait
{
    use DaTrait;
    use StatutBcTrait;

    //=====================================================================================
    // Styles des DA, OR, BC dans le css
    private $styleStatutDA = [];
    private $styleStatutOR = [];
    private $styleStatutBC = [];

    // Repository et model
    private DaModel $daModel;
    private DossierInterventionAtelierModel $dwModel;
    private AgenceRepository $agenceRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private UserDataService $userDataService;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaListeTrait()
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();

        $this->daModel = new DaModel();
        $this->dwModel = new DossierInterventionAtelierModel();
        $this->userDataService = new UserDataService($em);
        $this->agenceRepository = $em->getRepository(Agence::class);
        $this->daSoumissionBcRepository = $em->getRepository(DaSoumissionBc::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
    }
    //=====================================================================================

    private function initStyleStatuts()
    {
        $this->styleStatutDA = [
            DemandeAppro::STATUT_VALIDE               => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_TERMINER             => 'bg-primary text-white',
            DemandeAppro::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
            DemandeAppro::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
            DemandeAppro::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
            DemandeAppro::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
            DemandeAppro::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE  => 'bg-creation-demande-initiale',
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        ];
        $this->styleStatutOR = [
            DitOrsSoumisAValidation::STATUT_VALIDE                     => 'bg-or-valide',
            DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION => 'bg-a-resoumettre-a-validation',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_CA               => 'bg-or-valider-ca',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_DT               => 'bg-or-valider-dt',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_CLIENT           => 'bg-or-valider-client',
            DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CA       => 'bg-modif-demande-ca',
            DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CLIENT   => 'bg-modif-demande-client',
            DitOrsSoumisAValidation::STATUT_REFUSE_CA                  => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_REFUSE_CLIENT              => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_REFUSE_DT                  => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_SOUMIS_A_VALIDATION        => 'bg-or-soumis-validation',
            DemandeAppro::STATUT_DW_A_VALIDE                           => 'bg-or-soumis-validation',
            DemandeAppro::STATUT_DW_VALIDEE                            => 'bg-or-valide',
            DemandeAppro::STATUT_DW_A_MODIFIER                         => 'bg-modif-demande-client',
            DemandeAppro::STATUT_DW_REFUSEE                            => 'bg-or-non-valide',
        ];
        $this->styleStatutBC = [
            DaSoumissionBc::STATUT_A_GENERER                => 'bg-bc-a-generer',
            DaSoumissionBc::STATUT_A_EDITER                 => 'bg-bc-a-editer',
            DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION => 'bg-bc-a-soumettre-a-validation',
            DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR => 'bg-bc-a-envoyer-au-fournisseur',
            DaSoumissionBc::STATUT_SOUMISSION               => 'bg-bc-soumission',
            DaSoumissionBc::STATUT_A_VALIDER_DA             => 'bg-bc-a-valider-da',
            DaSoumissionBc::STATUT_NON_DISPO                => 'bg-bc-non-dispo',
            DaSoumissionBc::STATUT_VALIDE                   => 'bg-bc-valide',
            DaSoumissionBc::STATUT_CLOTURE                  => 'bg-bc-cloture',
            DaSoumissionBc::STATUT_REFUSE                   => 'bg-bc-refuse',
            DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR => 'bg-bc-envoye-au-fournisseur',
            DaSoumissionBc::STATUT_PAS_DANS_OR              => 'bg-bc-pas-dans-or',
            'Non validé'                                    => 'bg-bc-non-valide',
            DaSoumissionBc::STATUT_TOUS_LIVRES              => 'tout-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE      => 'partiellement-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO      => 'partiellement-dispo',
            DaSoumissionBc::STATUT_COMPLET_NON_LIVRE        => 'complet-non-livre',
        ];
    }

    public function getPaginationData(array $criteria, int $page, int $limit): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $userConnecter = $this->getUser();
        $codeAgence = $userConnecter->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findIdByCodeAgence($codeAgence);
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA($userConnecter, $criteria, $idAgenceUser, $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->estAdmin(), $page, $limit);
        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $paginationData['data'];

        // mise à jours des donner dans la base de donner
        $this->quelqueModifictionDansDatabase($daAffichers);

        // Vérification du verrouillage des DA et Retourne les DA filtrées
        $paginationData['data'] = $this->appliquerVerrouillageSelonProfil($daAffichers, $this->estAdmin(), $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier());

        return $paginationData;
    }

    private function quelqueModifictionDansDatabase(array $datas): void
    {
        $em = $this->getEntityManager();
        foreach ($datas as $data) {
            if ($data->getArtDesi() !== 'ECROU HEX. AC.GALVA A CHAUD CL.8 DI') {
                $this->modificationStatutBC($data, $em);
            }
        }
        $em->flush();
    }

    /**
     * Cette methode permet de modifier le statut du BC
     *
     * @return void
     */
    private function modificationStatutBC(DaAfficher $data, $em)
    {
        $statutBC = $this->statutBc($data->getArtRefp(), $data->getNumeroDemandeDit(), $data->getNumeroDemandeAppro(), $data->getArtDesi(), $data->getNumeroOr());
        $data->setStatutCde($statutBC);
        $em->persist($data);
    }

    /**
     * Applique le verrouillage ou déverrouillage des DA en fonction du profil utilisateur
     * 
     * @param iterable<DaAfficher> $daAffichers
     * @param bool $estAdmin
     * @param bool $estAppro
     * @param bool $estAtelier
     * 
     * @return iterable<DaAfficher>
     */
    private function appliquerVerrouillageSelonProfil(iterable $daAffichers, bool $estAdmin, bool $estAppro, bool $estAtelier): iterable
    {
        foreach ($daAffichers as $daAfficher) {
            $verrouille = $this->estDaVerrouillee(
                $daAfficher->getStatutDal(),
                $daAfficher->getStatutOr(),
                $estAdmin,
                $estAppro,
                $estAtelier,
                $daAfficher->getAchatDirect() && $daAfficher->getServiceEmetteur() == $this->userDataService->getServiceId($this->getUser())
            );
            $daAfficher->setVerouille($verrouille);
        }
        return $daAffichers;
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     *  @param DaAfficher[] $data données avant préparation
     **/
    private function prepareDataForDisplay(array $data): array
    {
        $datasPrepared = [];

        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        $statutDASupprimable = [DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE];

        // Roles
        $estAdmin   = $this->estAdmin();
        $estAppro   = $this->estUserDansServiceAppro();
        $estAtelier = $this->estUserDansServiceAtelier();

        // Initialiser le style pour les statuts
        $this->initStyleStatuts();

        foreach ($data as $item) {
            // Variables à employer
            $achatDirect = $item->getAchatDirect();

            // Pré-calculer les styles
            $styleStatutDA = $this->styleStatutDA[$item->getStatutDal()] ?? '';
            $styleStatutOR = $this->styleStatutOR[$item->getStatutOr()] ?? '';
            $styleStatutBC = $this->styleStatutBC[$item->getStatutCde()] ?? '';

            // Pré-calculer les booléens
            $ajouterDA = !$achatDirect && ($estAtelier || $estAdmin); // pas achat direct && (atelier ou admin)  
            $supprimable = ($estAppro || $estAtelier || $estAdmin) && in_array($item->getStatutDal(), $statutDASupprimable);
            $demandeDevis = ($estAppro || $estAdmin) && $item->getStatutDal() === DemandeAppro::STATUT_SOUMIS_APPRO;
            $statutOrValide = $item->getStatutOr() === DitOrsSoumisAValidation::STATUT_VALIDE;
            $pathOrMax = $this->dwModel->findCheminOrVersionMax($item->getNumeroOr());
            $telechargerOR = $statutOrValide && !empty($pathOrMax);

            // Construction d'urls
            $urls = $this->buildItemUrls($item, $ajouterDA);

            // Statut OR | Statut DocuWare
            $statutOR = $item->getStatutOr();
            if (!$achatDirect && !empty($statutOR)) {
                $statutOR = "OR - $statutOR";
            }

            // Tout regrouper
            $datasPrepared[] = [
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
                'statutOr'            => $statutOR,
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
                'styleStatutDA'       => $styleStatutDA,
                'styleStatutOR'       => $styleStatutOR,
                'styleStatutBC'       => $styleStatutBC,
                'urlCreation'         => $urls['creation'],
                'urlDetail'           => $urls['detail'],
                'urlDesignation'      => $urls['designation'],
                'urlDelete'           => $urls['delete'],
                'urlDemandeDevis'     => $urls['demandeDevis'],
                'ajouterDA'           => $ajouterDA,
                'supprimable'         => $supprimable,
                'demandeDevis'        => $demandeDevis,
                'telechargerOR'       => $telechargerOR,
                'pathOrMax'           => $pathOrMax,
                'statutValide'        => $item->getStatutDal() === DemandeAppro::STATUT_VALIDE,
            ];
        }

        return $datasPrepared;
    }

    /**
     * Construit l'ensemble des URLs associées à un item de demande d'approvisionnement.
     *
     * @param DaAfficher $item Objet métier utilisé pour déterminer les routes.
     * @param bool       $ajouterDA savoir si il faut ajouter le bouton de l'ajout de DA.
     *
     * @return array{detail:string,designation:string,delete:string,demandeDevis:string,creation:string}
     */
    private function buildItemUrls(DaAfficher $item, bool $ajouterDA): array
    {
        $urls = [];

        // URL création de DA avec DIT
        $urls['creation'] = $ajouterDA ? $this->getUrlGenerator()->generate('da_new_avec_dit', [
            'daId'  => 0,
            'ditId' => $item->getDit()->getId(),
        ]) : '';

        // URL détail
        $urls['detail'] = $this->getUrlGenerator()->generate(
            $item->getAchatDirect() ? 'da_detail_direct' : 'da_detail_avec_dit',
            ['id' => $item->getDemandeAppro()->getId()]
        );

        // URL désignation (peut basculer sur "new" si statut en cours de création)
        $urls['designation'] = $item->getStatutDal() === DemandeAppro::STATUT_EN_COURS_CREATION
            ? $this->getUrlGenerator()->generate('da_new_avec_dit', [
                'daId'  => $item->getDemandeAppro()->getId(),
                'ditId' => $item->getDit()->getId(),
            ])
            : $this->getUrlGenerator()->generate(
                $item->getAchatDirect() ? 'da_proposition_direct' : 'da_proposition_ref_avec_dit',
                ['id' => $item->getDemandeAppro()->getId()]
            );

        // URL suppression de ligne
        $urls['delete'] = $this->getUrlGenerator()->generate(
            $item->getAchatDirect() ? 'da_delete_line_direct' : 'da_delete_line_avec_dit',
            ['numDa' => $item->getNumeroDemandeAppro(), 'ligne' => $item->getNumeroLigne()]
        );

        // URL demande de devis
        $urls['demandeDevis'] = $this->getUrlGenerator()->generate(
            'da_demande_devis_en_cours',
            ['id' => $item->getDemandeAppro()->getId()]
        );

        return $urls;
    }

    public function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];

        $daSearch->toObject($criteria);
    }
}
