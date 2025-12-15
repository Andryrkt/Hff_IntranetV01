<?php

namespace App\Controller\Traits\da;

use Twig\Markup;
use App\Model\da\DaModel;
use App\Entity\da\DaSearch;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Entity\admin\utilisateur\Role;
use App\Repository\admin\AgenceRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaSoumissionBcRepository;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

trait DaListeTrait
{
    use DaTrait;
    use StatutBcTrait;
    use MarkupIconTrait;

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

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaListeTrait()
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();

        $this->daModel = new DaModel();
        $this->dwModel = new DossierInterventionAtelierModel();
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
            DemandeAppro::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
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
            //statut pour DA Reappro
            DaSoumissionBc::STATUT_CESSION_A_GENERER        => 'bg-bc-cession-a-generer',
            DaSoumissionBc::STATUT_EN_COURS_DE_PREPARATION  => 'bg-bc-en-cours-de-preparation',
            //statut pour DA Reappro, DA direct, DA via OR
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
        $paginationData = $this->daAfficherRepository->findPaginatedAndFilteredDA($userConnecter, $criteria, $idAgenceUser, $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->hasRoles(Role::ROLE_ADMINISTRATEUR), $page, $limit);
        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $paginationData['data'];

        // mise à jours des donner dans la base de donner
        $this->quelqueModifictionDansDatabase($daAffichers);

        // Vérification du verrouillage des DA et Retourne les DA filtrées
        $paginationData['data'] = $this->appliquerVerrouillageSelonProfil($daAffichers, $this->hasRoles(Role::ROLE_ADMINISTRATEUR), $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->hasRoles(Role::ROLE_DA_DIRECTE));

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
        $statutBC = $this->statutBc($data);
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
     * @param bool $estCreateurDaDirecte
     * 
     * @return iterable<DaAfficher>
     */
    private function appliquerVerrouillageSelonProfil(iterable $daAffichers, bool $estAdmin, bool $estAppro, bool $estAtelier, bool $estCreateurDaDirecte): iterable
    {
        foreach ($daAffichers as $daAfficher) {
            $verrouille = $this->estDaVerrouillee(
                $daAfficher->getStatutDal(),
                $daAfficher->getStatutOr(),
                $estAdmin,
                $estAppro,
                $estAtelier,
                $estCreateurDaDirecte
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

        $daType = [
            DemandeAppro::TYPE_DA_AVEC_DIT => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT   => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO  => $this->getIconDaReappro(),
        ];

        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        $statutDASupprimable = [DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE];

        // Roles
        $estAdmin   = $this->hasRoles(Role::ROLE_ADMINISTRATEUR);
        $estAppro   = $this->estUserDansServiceAppro();
        $estAtelier = $this->estUserDansServiceAtelier();

        // Initialiser le style pour les statuts
        $this->initStyleStatuts();

        foreach ($data as $item) {
            // Variables à employer
            $daReappro = $item->getDaTypeId() == DemandeAppro::TYPE_DA_REAPPRO;
            $daDirect = $item->getDaTypeId() == DemandeAppro::TYPE_DA_DIRECT;
            $daViaOR = $item->getDaTypeId() == DemandeAppro::TYPE_DA_AVEC_DIT;
            $envoyeFrn = $item->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;

            // Pré-calculer les styles
            $styleStatutDA = $this->styleStatutDA[$item->getStatutDal()] ?? '';
            $styleStatutOR = $this->styleStatutOR[$item->getStatutOr()] ?? '';
            $styleStatutBC = $this->styleStatutBC[$item->getStatutCde()] ?? '';

            // Pré-calculer les booléens
            $ajouterDA = $daViaOR && ($estAtelier || $estAdmin); // da via OR && (atelier ou admin)  
            $supprimable = ($estAppro || $estAtelier || $estAdmin) && in_array($item->getStatutDal(), $statutDASupprimable) && !$daReappro;
            $demandeDevis = ($estAppro || $estAdmin) && $item->getStatutDal() === DemandeAppro::STATUT_SOUMIS_APPRO && !$daReappro;
            $dataOR = $this->dwModel->findCheminOrDernierValide($item->getNumeroDemandeDit(), $item->getNumeroDemandeAppro());

            // Construction d'urls
            $urls = $this->buildItemUrls($item, $ajouterDA, $item->getDaTypeId());

            // Statut OR | Statut DocuWare
            $statutOR = $item->getStatutOr();
            if ($daViaOR && !empty($statutOR)) $statutOR = "OR - $statutOR";

            // Préparer attributs pour la balise <a> de la date de livraison prévue
            $aDtLivPrevAttributes = [
                'href'               => '#',
                "data-bs-toggle"     => "modal",
                "data-bs-target"     => "#dateLivraison",
                "data-numero-cde"    => $item->getNumeroCde(),
                "data-date-actuelle" => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('Y-m-d') : '',
            ];


            // Tout regrouper
            $datasPrepared[] = [
                'dit'                 => $item->getDit(),
                'objet'               => $item->getObjetDal(),
                'numeroDemandeAppro'  => $item->getNumeroDemandeAppro(),
                'demandeAppro'        => $item->getDemandeAppro(),
                'datype'              => $daType[$item->getDaTypeId()],
                'numeroDemandeDit'    => $daViaOR ? $item->getNumeroDemandeDit() : $safeIconBan,
                'numeroOr'            => $daDirect ? $safeIconBan : $item->getNumeroOr(),
                'niveauUrgence'       => $daReappro ? $safeIconBan : $item->getNiveauUrgence(),
                'demandeur'           => $item->getDemandeur(),
                'dateDemande'         => $item->getDateDemande() ? $item->getDateDemande()->format('d/m/Y') : '',
                'statutDal'           => $item->getStatutDal(),
                'statutOr'            => $statutOR,
                'statutCde'           => $item->getStatutCde(),
                'datePlannigOr'       => $daViaOR ? ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : '') : $safeIconBan,
                'nomFournisseur'      => $item->getNomFournisseur(),
                'artConstp'           => $item->getArtConstp(),
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
                'dateFinSouhaite'     => $item->getDateFinSouhaite() ? $item->getDateFinSouhaite()->format('d/m/Y') : 'N/A',
                'dateLivraisonPrevue' => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('d/m/Y') : 'N/A',
                'joursDispo'          => $item->getJoursDispo() ?? '',
                'styleJoursDispo'     => $item->getJoursDispo() && $item->getJoursDispo() < 0 ? 'text-danger' : '',
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
                'telechargerOR'       => !empty($dataOR),
                'pathOr'              => empty($dataOR) ? '' : $dataOR['chemin'],
                'statutValide'        => $item->getStatutDal() === DemandeAppro::STATUT_VALIDE,
                'centrale'            => $daReappro ? $item->getDesiCentrale() : $safeIconBan,
                'envoyeFrn'            => $envoyeFrn,
                'aDtLivPrevAttributes' => $aDtLivPrevAttributes,
            ];
        }

        return $datasPrepared;
    }

    /**
     * Construit l'ensemble des URLs associées à un item de demande d'approvisionnement.
     *
     * @param DaAfficher $item Objet métier utilisé pour déterminer les routes.
     * @param bool       $ajouterDA savoir si il faut ajouter le bouton de l'ajout de DA.
     * @param int        $daTypeId Id du type de la DA.
     *
     * @return array{detail:string,designation:string,delete:string,demandeDevis:string,creation:string}
     */
    private function buildItemUrls(DaAfficher $item, bool $ajouterDA, int $daTypeId): array
    {
        $urls = [];
        $routeNames = [
            'creation' => [
                DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_new_avec_dit',
                DemandeAppro::TYPE_DA_DIRECT    => 'da_new_direct',
                DemandeAppro::TYPE_DA_REAPPRO   => 'da_new_reappro',
            ],
            'detail' => [
                DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_detail_avec_dit',
                DemandeAppro::TYPE_DA_DIRECT    => 'da_detail_direct',
                DemandeAppro::TYPE_DA_REAPPRO   => 'da_detail_reappro',
            ],
            'proposition' => [
                DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_proposition_ref_avec_dit',
                DemandeAppro::TYPE_DA_DIRECT    => 'da_proposition_direct',
                DemandeAppro::TYPE_DA_REAPPRO   => 'da_validate_reappro',
            ],
            'delete' => [
                DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_delete_line_avec_dit',
                DemandeAppro::TYPE_DA_DIRECT    => 'da_delete_line_direct',
            ],
        ];

        $parametres = [
            'daId'           => ['id'    => $item->getDemandeAppro()->getId()],
            'numDa-numLigne' => ['numDa' => $item->getNumeroDemandeAppro(), 'ligne' => $item->getNumeroLigne()],
        ];

        if ($daTypeId === DemandeAppro::TYPE_DA_AVEC_DIT) {
            $parametres['daId-0-ditId'] = ['daId'  => 0,                                 'ditId' => $item->getDit()->getId(),];
            $parametres['daId-ditId']   = ['daId'  => $item->getDemandeAppro()->getId(), 'ditId' => $item->getDit()->getId(),];
        }

        // URL création de DA avec DIT
        $urls['creation'] = $ajouterDA ? $this->getUrlGenerator()->generate($routeNames['creation'][0], $parametres['daId-0-ditId']) : '';

        // URL détail
        $urls['detail'] = $this->getUrlGenerator()->generate($routeNames['detail'][$daTypeId], $parametres['daId']);

        // URL désignation (peut basculer sur "new" si statut en cours de création)
        $urls['designation'] = $item->getStatutDal() === DemandeAppro::STATUT_EN_COURS_CREATION
            ? $this->getUrlGenerator()->generate($routeNames['creation'][$daTypeId], $daTypeId === DemandeAppro::TYPE_DA_AVEC_DIT ? $parametres['daId-ditId'] : $parametres['daId'])
            : $this->getUrlGenerator()->generate($routeNames['proposition'][$daTypeId], $parametres['daId']);

        // URL suppression de ligne
        $urls['delete'] = $daTypeId != DemandeAppro::TYPE_DA_REAPPRO ? $this->getUrlGenerator()->generate($routeNames['delete'][$daTypeId], $parametres['numDa-numLigne']) : '';

        // URL demande de devis
        $urls['demandeDevis'] = $this->getUrlGenerator()->generate('da_demande_devis_en_cours', $parametres['daId']);

        return $urls;
    }

    public function initialisationRechercheDa(DaSearch $daSearch)
    {
        $criteria = $this->getSessionService()->get('criteria_search_list_da', []) ?? [];

        $daSearch->toObject($criteria, $this->agServCriteria($criteria));
    }

    private function agServCriteria(array $criteria): array
    {
        $agServ = [
            'agenceEmetteur'  => isset($criteria['agenceEmetteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceEmetteur']) : null,
            'agenceDebiteur'  => isset($criteria['agenceDebiteur']) ? $this->getEntityManager()->getRepository(Agence::class)->find($criteria['agenceDebiteur']) : null,
            'serviceEmetteur' => isset($criteria['serviceEmetteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceEmetteur']) : null,
            'serviceDebiteur' => isset($criteria['serviceDebiteur']) ? $this->getEntityManager()->getRepository(Service::class)->find($criteria['serviceDebiteur']) : null,
        ];

        return $agServ;
    }
}
