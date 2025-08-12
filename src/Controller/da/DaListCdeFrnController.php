<?php

namespace App\Controller\da;


use App\Entity\da\DaValider;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Model\da\DaListeCdeFrnModel;
use App\Service\TableauEnStringService;
use App\Form\da\daCdeFrn\CdeFrnListType;
use Symfony\Component\Form\FormInterface;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Model\da\DaModel;
use App\Repository\da\DemandeApproRepository;
use App\Entity\da\DemandeAppro;
use App\Repository\da\DaSoumissionBcRepository;
use App\Entity\da\DaSoumissionBc;


/**
 * @Route("/demande-appro")
 */
class DaListCdeFrnController extends Controller
{
    use DaTrait;

    private DaValiderRepository $daValiderRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DaModel $daModel;
    private DemandeApproRepository $daRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    

    public function __construct()
    {
        parent::__construct();
        $this->daValiderRepository = self::$em->getRepository(DaValider::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->daModel = new DaModel();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
    }

    /**
     * @Route("/da-list-cde-frn", name ="da_list_cde_frn" )
     */
    public function index(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** ===  Formulaire pour la recherche === */
        $form = self::$validator->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaireRecherche($request, $form);

        /** ==== récupération des données à afficher ==== */
        $daValides = $this->donnerAfficher($criteria);

        /** === Formulaire pour l'envoie de BC et FAC + Bl === */
        $formSoumission = self::$validator->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        /** Actualisation donner davalier */
        foreach ($daValides as $key => $davalide) {
            $statutBC = $this->statutBc( $davalide->getArtRefp(), $davalide->getNumeroDemandeDit(), $davalide->getNumeroDemandeAppro(), $davalide->getArtDesi(), $davalide->getNumeroOr());
            $davalide->setStatutCde($statutBC);
        }

        
        self::$twig->display('da/daListCdeFrn.html.twig', [
            'daValides' => $daValides,
            'formSoumission' => $formSoumission->createView(),
            'form' => $form->createView(),
        ]);
    }

    private function donnerAfficher(?array $criteria): array
    {
        /** récupération des ors Zst validé sous forme de tableau */
        $numOrValide = $this->ditOrsSoumisAValidationRepository->findNumOrValide();
        $numOrString = TableauEnStringService::TableauEnString(',', $numOrValide);
        $numOrValideZst = $this->daListeCdeFrnModel->getNumOrValideZst($numOrString);

        /** @var array récupération des lignes de daValider avec version max et or valider */
        $daValiders =  $this->daValiderRepository->getDaOrValider($numOrValideZst, $criteria);

        return $daValiders;
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
            return null;
        }

        $data = $form->getData();

        // Filtrer les champs vides ou nuls
        $dataFiltrée = array_filter($data, fn($val) => $val !== null && $val !== '');

        return empty($dataFiltrée) ? null : $data;
    }

     /**
     * @Route(path="/changement-statuts-envoyer-fournisseur/{numCde}/{datePrevue}/{estEnvoyer}", name="changement_statut_envoyer_fournisseur")
     *
     * @return void
     */
    public function changementStatutEnvoyerFournisseur(string $numCde = '', string $datePrevue = '', bool $estEnvoyer = false)
    {
        $this->verifierSessionUtilisateur();

        if ($estEnvoyer) {
            // modification de statut dans la soumission bc
            $numVersionMaxSoumissionBc = $this->daSoumissionBcRepository->getNumeroVersionMax($numCde);
            $soumissionBc = $this->daSoumissionBcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxSoumissionBc]);
            if ($soumissionBc) {
                $soumissionBc->setStatut(DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR);
                self::$em->persist($soumissionBc);
            }

            //modification dans la table da_valider
            $numVersionMaxDaValider = $this->daValiderRepository->getNumeroVersionMaxCde($numCde);
            $daValider = $this->daValiderRepository->findBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxDaValider]);
            foreach ($daValider as $valider) {
                $valider->setStatutCde(DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR)
                    ->setDateLivraisonPrevue(new \DateTime($datePrevue))
                ;
                self::$em->persist($valider);
            }
            self::$em->flush();
            // envoyer une notification de succès
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'statut modifié avec succès.']);
            $this->redirectToRoute("da_list_cde_frn");
        } else {
            $this->sessionService->set('notification', ['type' => 'error', 'message' => 'Erreur lors de la modification du statut... vous n\'avez pas cocher la cage à cocher.']);
            $this->redirectToRoute("da_list_cde_frn");
        }
    }
}
