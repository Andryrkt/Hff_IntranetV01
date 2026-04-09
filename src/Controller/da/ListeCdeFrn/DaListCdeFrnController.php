<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Controller\Controller;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Factory\da\CdeFrnDto\CdeFrnSearchDto;
use App\Form\da\daCdeFrn\CdeFrnListType;
use App\Form\da\daCdeFrn\DaModalDateLivraisonType;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Mapper\Da\DaAfficherMapper;
use App\Model\da\DaModel;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    use MarkupIconTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;


    public function __construct()
    {
        parent::__construct();
        $em = $this->getEntityManager();
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = $em->getRepository(DaSoumissionBc::class);
    }

    /**
     * @Route("/da-list-cde-frn", name="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /** ===  Formulaire pour la recherche === */
        $searchDto = $this->initialisationCdeFrnSearchDto();
        $form = $this->getFormFactory()->createBuilder(CdeFrnListType::class, $searchDto, [
            'em' => $this->getEntityManager(),
            'method' => 'GET',
        ])->getForm();

        $criteriaTab = $this->traitementFormulaireRecherche($request, $form) ?? $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn') ?? [];

        // Visuel de tri
        $sortJoursClass = false;
        if (isset($criteriaTab['sortNbJours'])) {
            $sortJoursClass = $criteriaTab['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }

        // Si "afficherCloturees" n'est pas dans les critères, on l'ajoute avec la valeur false
        if (!array_key_exists('afficherCloturees', $criteriaTab)) {
            $criteriaTab['afficherCloturees'] = false;
        }

        $page = $request->query->getInt('page', 1);
        $limit = 250;

        // Récupération et préparation des données
        $paginationData = $this->daAfficherRepository->findValidatedPaginatedDas($criteriaTab, $page, $limit, $codeSociete);
        $daAfficherMapper = new DaAfficherMapper($this->getUrlGenerator());
        $dataPrepared = $daAfficherMapper->mapList($paginationData['data'], [
            'estAdmin'   => $this->estAdmin(),
            'estAppro'   => $this->estUserDansServiceAppro(),
            'estAtelier' => $this->estUserDansServiceAtelier(),
            'estCreateur' => $this->estCreateurDeDADirecte(),
            'codeAgenceUser' => $this->getUser()->getCodeAgenceUser(),
            'codeServiceUser' => $this->getUser()->getCodeServiceUser(),
        ]);

        // Formulaire de soumission BC, FAC + BL, BL Reappro
        $formSoumission = $this->getFormFactory()->createBuilder(DaSoumissionType::class, null, ['method' => 'GET'])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        // Formulaire de date de livraison
        $formDateLivraison = $this->getFormFactory()->createBuilder(DaModalDateLivraisonType::class)->getForm();
        $this->TraitementFormulaireDateLivraison($request, $formDateLivraison);

        return $this->render('da/daListCdeFrn.html.twig', [
            'data'              => $dataPrepared,
            'formSoumission'    => $formSoumission->createView(),
            'form'              => $form->createView(),
            'criteria'          => $criteriaTab,
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
            $data = $formDateLivraison->getData();
            $dateLivraisonPrevue = $data['dateLivraisonPrevue'];
            $daAffichers = $this->daAfficherRepository->findBy(['numeroCde' => $data['numeroCde']]);

            /** @var DaAfficher $daAfficher */
            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setDateLivraisonPrevue($dateLivraisonPrevue)
                    ->setJoursDispo($dateLivraisonPrevue->diff(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->days);
                $this->getEntityManager()->persist($daAfficher);
            }

            $this->getEntityManager()->flush();
            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Date de livraison prévue modifiée avec succès']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }

    private function initialisationCdeFrnSearchDto(): CdeFrnSearchDto
    {
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn') ?? [];
        return (new CdeFrnSearchDto())->toObject($criteriaTab);
    }

    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();
            $params = ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']];

            if ($soumission['soumission'] === 'BC') {
                $this->redirectToRoute("da_soumission_bc", $params);
            } elseif ($soumission['soumission'] === 'Facture + BL') {
                $this->redirectToRoute("da_soumission_facbl", $params);
            } elseif ($soumission['soumission'] === 'BL Reappro') {
                $this->redirectToRoute("da_soumission_bl_reappro", $params);
            }
        }
    }

    private function traitementFormulaireRecherche(Request $request, FormInterface $form): ?array
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteriaTab = $form->getData()->toArray() ?? [];
            $this->getSessionService()->set('criteria_for_excel_Da_Cde_frn', $criteriaTab);
            return $criteriaTab;
        }

        return null;
    }
}
