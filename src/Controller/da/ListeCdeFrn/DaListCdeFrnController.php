<?php

namespace App\Controller\da\ListeCdeFrn;


use App\Model\da\DaModel;;

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
     * @Route("/da-list-cde-frn", name ="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP, Service::ID_APPRO);
        /** FIN AUtorisation acées */

        /** ===  Formulaire pour la recherche === */
        $form = $this->getFormFactory()->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaireRecherche($request, $form);
        $this->getSessionService()->set('criteria_for_excel_Da_Cde_frn', $criteria);

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        /** ==== récupération des données à afficher ==== */
        $paginationData = $this->getPaginationData($criteria, $page, $limit);

        /** mise à jour des donners daAfficher */
        $this->quelqueMiseAjourDaAfficher($paginationData['data']);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = $this->getFormFactory()->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        return $this->render('da/daListCdeFrn.html.twig', [
            'daAfficherValides' => $paginationData['data'],
            'formSoumission'    => $formSoumission->createView(),
            'form'              => $form->createView(),
            'styleStatutBC'     => $this->styleStatutBC,
            'styleStatutDA'     => $this->styleStatutDA,
            'styleStatutOR'     => $this->styleStatutOR,
            'criteria'          => $criteria,
            'currentPage'       => $page,
            'totalPages'        => $paginationData['lastPage'],
            'resultat'          => $paginationData['totalItems'],
        ]);
    }

    private function quelqueMiseAjourDaAfficher(array $daAfficherValides)
    {
        foreach ($daAfficherValides as $davalide) {
            $this->modificationStatutBC($davalide);
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
        $statutBC = $this->statutBc($data->getArtRefp(), $data->getNumeroDemandeDit(), $data->getNumeroDemandeAppro(), $data->getArtDesi(), $data->getNumeroOr());
        $data->setStatutCde($statutBC);
        $this->getEntityManager()->persist($data);
    }

    private function donnerAfficher(?array $criteria): array
    {
        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daAfficherValiders =  $this->daAfficherRepository->getDaOrValider($criteria);

        return $daAfficherValiders;
    }

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

        $data = $form->getData();

        // Filtrer les champs vides ou nuls
        $dataFiltered = array_filter($data, fn($val) => $val !== null && $val !== '');

        return empty($dataFiltered) ? [] : $data;
    }
}
