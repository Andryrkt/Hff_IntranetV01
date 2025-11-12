<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Model\da\DaModel;;

use Twig\Markup;
use App\Entity\admin\Service;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Form\da\daCdeFrn\CdeFrnListType;
use Symfony\Component\Form\FormInterface;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Controller\Traits\da\StatutBcTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Controller\Traits\AutorisationTrait;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Factory\da\CdeFrnDto\CdeFrnSearchDto;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    use StatutBcTrait;
    use AutorisationTrait;
    use MarkupIconTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;


    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
        $this->ditOrsSoumisAValidationRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->demandeApproRepository = $this->getEntityManager()->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = $this->getEntityManager()->getRepository(DaSoumissionBc::class);
        $this->initStatutBcTrait();
    }

    /**
     * @Route("/da-list-cde-frn", name="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP, Service::ID_APPRO);
        /** FIN AUtorisation acées */

        /** ===  Formulaire pour la recherche === */
        $form = $this->getFormFactory()->createBuilder(CdeFrnListType::class, $this->initialisationCdeFrnSearchDto(), [
            'method' => 'GET',
        ])->getForm();
        $criteriaTab = $this->traitementFormulaireRecherche($request, $form);
        $this->getSessionService()->set('criteria_for_excel_Da_Cde_frn', $criteriaTab);

        // classe pour visuel de trie nombre de jour dispo
        $sortJoursClass = false;

        if ($criteriaTab &&  isset($criteriaTab['sortNbJours'])) {
            $sortJoursClass = $criteriaTab['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        /** ==== récupération des données à afficher ==== */
        $paginationData = $this->getPaginationData($criteriaTab, $page, $limit);

        /** mise à jour des donners daAfficher */
        $this->quelqueMiseAjourDaAfficher($paginationData['data']);

        /** Préparer les données à afficher dans twig */
        $dataPrepared = $this->prepareDataForDisplay($paginationData['data']);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = $this->getFormFactory()->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        /** === Formulaire pour la date de livraison prevu === */
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);

        return $this->render('da/daListCdeFrn.html.twig', [
            'data'              => $dataPrepared,
            'formSoumission'    => $formSoumission->createView(),
            'form'              => $form->createView(),
            'criteria'          => $criteriaTab,
            'daTypeIcons'       => $this->getAllIcons(),
            'currentPage'       => $page,
            'totalPages'        => $paginationData['lastPage'],
            'resultat'          => $paginationData['totalItems'],
            'sortJoursClass'    => $sortJoursClass,
            'formDateLivraison' => $formDateLivraison->createView()
        ]);
    }

    private function TraitementFormulaireDateLivraison(Request $request, FormInterface $formDateLivraison)
    {
        $formDateLivraison->handleRequest($request);

        if ($formDateLivraison->isSubmitted() && $formDateLivraison->isValid()) {
            //recupération des valeurs dans le formulaire
            $data = $formDateLivraison->getData();

            // recupération des lignes de commande dans le da_afficher
            $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroCde' => $data['numeroCde']]);

            //modification de la date livraison prevue sur chaque ligne
            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($data['dateLivraisonPrevue']);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Date de livraison prévue modifier avec succèss']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }


    private function initialisationCdeFrnSearchDto(): CdeFrnSearchDto
    {
        // recupération de la session pour le criteria
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');

        // transforme en objet
        $cdeFrnSearchDto = new CdeFrnSearchDto();
        return $cdeFrnSearchDto->toObject($criteriaTab);
    }

    private function quelqueMiseAjourDaAfficher(array $daAfficherValides)
    {

        foreach ($daAfficherValides as $davalide) {
            if ($davalide->getArtDesi() !== 'ECROU HEX. AC.GALVA A CHAUD CL.8 DI') {
                $this->modificationStatutBC($davalide);
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Cette methode permet de modifier le statut du BC
     *
     * @return void
     */
    private function modificationStatutBC(DaAfficher $data)
    {
        $statutBC = $this->statutBc($data);
        $data->setStatutCde($statutBC);
        $this->getEntityManager()->persist($data);
    }

    // private function donnerAfficher(?array $criteria): array
    // {
    //     /** @var array récupération des lignes de daValider avec version max et or valider */
    //     $daAfficherValiders =  $this->daAfficherRepository->getDaOrValider($criteria);

    //     return $daAfficherValiders;
    // }

    /** 
     * Fonction qui retourne les données avec pagination des lignes de DA validé et OR validés
     * 
     * @param null|array $criteria le criteria du formulaire de recherche
     * @param int $page la page actuelle
     * @param int $limit la limite par page
     * 
     * @return array{results:array,totalItems:int}
     */
    private function getPaginationData(?array $criteria, int $page, int $limit): array
    {
        return $this->daAfficherRepository->findValidatedPaginatedDas($criteria, $page, $limit);
    }

    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();

            if ($soumission['soumission'] === true) {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            } else {
                $this->redirectToRoute("da_soumission_facbl", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            }
        }
    }

    private function traitementFormulaireRecherche(Request $request, FormInterface $form): ?array
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return [];
        }

        $data = $form->getData()->toArray();

        // Filtrer les champs vides ou nuls
        $dataFiltered = array_filter($data, fn($val) => $val !== null && $val !== '');

        return empty($dataFiltered) ? [] : $data;
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

        $routeDetailName = [
            DemandeAppro::TYPE_DA_DIRECT   => 'da_detail_direct',
            DemandeAppro::TYPE_DA_AVEC_DIT => 'da_detail_avec_dit',
            DemandeAppro::TYPE_DA_REAPPRO  => '',
        ];

        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        foreach ($data as $item) {
            // Variables à employer
            $daDirect = $item->getDaTypeId() == DemandeAppro::TYPE_DA_DIRECT;
            $daViaOR = $item->getDaTypeId() == DemandeAppro::TYPE_DA_AVEC_DIT;
            $envoyeFrn = $item->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;

            // Si numeroCde est vide ou null, on met un '-'
            $numeroCde = !empty($item->getNumeroCde()) ? $item->getNumeroCde() : '-';

            // Préparer les classes et attributs pour le <td> du numéro cde
            if (!empty($item->getNumeroCde())) {
                $tdNumCdeAttributes = [
                    'class'             => 'text-center commande-cellule commande',
                    'data-commande-id'  => $item->getNumeroCde(),
                    'data-num-da'       => $item->getNumeroDemandeAppro(),
                    'data-num-or'       => $item->getNumeroOr(),
                    'data-statut-bc'    => $item->getStatutCde(),
                    'data-position-cde' => $item->getPositionBc(),
                ];
            } else {
                $tdNumCdeAttributes = [
                    'class'             => 'text-center'
                ];
            }

            // Préparer les classes et attributs pour le <td> du numéro cde
            $tdCheckboxAttributes = [
                'class'                     => 'modern-checkbox',
                'type'                      => 'checkbox',
                'value'                     => $item->getId(),
                'data-numero-demande-appro' => $item->getNumeroDemandeAppro(),
                'data-numero-ligne'         => $item->getNumeroLigne(),
            ];

            // Pré-calculer les styles
            $styleStatutDA = $this->styleStatutDA[$item->getStatutDal()] ?? '';
            $styleStatutBC = $this->styleStatutBC[$item->getStatutCde()] ?? '';
            $styleClickableCell = $envoyeFrn ? 'clickable-td' : '';

            // Construction d'urls
            $urlDetail = $this->getUrlGenerator()->generate(
                $routeDetailName[$item->getDaTypeId()],
                ['id' => $item->getDemandeAppro()->getId()]
            );

            // Tout regrouper
            $datasPrepared[] = [
                'urlDetail'          => $urlDetail,
                'numeroDemandeAppro' => $item->getNumeroDemandeAppro(),
                'datype'              => $daType[$item->getDaTypeId()],
                'numeroDemandeDit'   => $item->getNumeroDemandeDit() ?? $safeIconBan,
                'niveauUrgence'      => $item->getNiveauUrgence(),
                'numeroOr'           => $daDirect ? $safeIconBan : $item->getNumeroOr(),
                'datePlannigOr'      => $daViaOR ? ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : '') : $safeIconBan,
                'numeroFournisseur'  => $item->getNumeroFournisseur(),
                'nomFournisseur'     => $item->getNomFournisseur(),
                'numeroCde'          => $numeroCde,
                'tdNumCdeAttributes' => $tdNumCdeAttributes,
                'styleStatutDA'      => $styleStatutDA,
                'styleStatutBC'      => $styleStatutBC,
                'statutDal'          => $item->getStatutDal(),
                'statutCde'          => $item->getStatutCde(),
                'envoyeFrn'          => $envoyeFrn,
                'styleClickableCell' => $styleClickableCell,
                'tdCheckboxAttributes' => $tdCheckboxAttributes,
                'dateFinSouhaite'     => $item->getDateFinSouhaite() ? $item->getDateFinSouhaite()->format('d/m/Y') : '',
                'artRefp'             => $item->getArtRefp(),
                'artDesi'             => $item->getArtDesi(),
                'qteDem'              => $item->getQteDem() == 0 ? '-' : $item->getQteDem(),
                'qteEnAttent'         => $item->getQteEnAttent() == 0 ? '-' : $item->getQteEnAttent(),
                'qteDispo'            => $item->getQteDispo() == 0 ? '-' : $item->getQteDispo(),
                'qteLivrer'           => $item->getQteLivrer() == 0 ? '-' : $item->getQteLivrer(),
                'dateLivraisonPrevue' => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('d/m/Y') : '',
                'joursDispo'          => $item->getJoursDispo(),
                'styleJoursDispo'     => $item->getJoursDispo() < 0 ? 'text-danger' : '',
                'demandeur'           => $item->getDemandeur(),
            ];
        }

        return $datasPrepared;
    }
}
