<?php

namespace App\Controller\da\Proposition;

use App\Service\UserData\UserDataService;
use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DaObservationType;
use App\Form\da\DaPropositionValidationType;
use App\Form\da\DemandeApproLRCollectionType;
use App\Service\da\DocRattacheService;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Repository\da\DemandeApproRepository;
use App\Service\da\DaObservationService;
use App\Service\da\DaPropositionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaPropositionArticleDirectController extends Controller
{
    private UserDataService $userDataService;
    private DemandeApproRepository $demandeApproRepository;
    private DocRattacheService $docRattacheService;
    private UrlIdCipher $urlIdCipher;
    private DaObservationService $daObservationService;
    private DaPropositionService $daPropositionService;

    public function __construct(
        EntityManagerInterface $em,
        DocRattacheService $docRattacheService,
        DaObservationService $daObservationService,
        DaPropositionService $daPropositionService,
        UserDataService $userDataService
    ) {
        $this->userDataService = $userDataService;
        $this->docRattacheService = $docRattacheService;
        $this->daObservationService = $daObservationService;
        $this->daPropositionService = $daPropositionService;
        $this->urlIdCipher = new UrlIdCipher;
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
    }

    /**
     * @Route("/proposition-direct/{token}", name="da_proposition_direct")
     */
    public function propositionDeReference(string $token, Request $request)
    {
        $id = $this->urlIdCipher->decryptInt($token);

        if (empty($id) && $id !== 0) throw new ResourceNotFoundException();

        $da = $this->demandeApproRepository->find($id);
        $numDa = $da->getNumeroDemandeAppro();

        $daObservation = new DaObservation();
        $DapLRCollection = new DemandeApproLRCollection();
        $form = $this->getFormFactory()->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();
        $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $da->getDaTypeId()])->getForm();
        $formValidation = $this->getFormFactory()->createBuilder(
            DaPropositionValidationType::class,
            [],
            [
                'action' => $this->getUrlGenerator()->generate('da_validate_direct', ['numDa' => $numDa])
            ]
        )->getForm();

        // Traitement du formulaire en général ===========================//
        $this->traitementFormulaire($form, $formObservation, $request, $da); //
        // ===============================================================//

        $observations = $this->daObservationService->getObservations($numDa);

        $fichiers = $this->docRattacheService->getAllAttachedFiles($da);

        return $this->render("da/proposition.html.twig", [
            'demandeAppro'            => $da,
            'id'                      => $id,
            'urlRetour'               => $this->getUrlGenerator()->generate('list_da'),
            'titreBoutonRetour'       => 'Liste des demandes d’achats',
            'urlModifierDa'           => $this->getUrlGenerator()->generate('da_edit_direct', ['token' => $token]),
            'form'                    => $form->createView(),
            'formValidation'          => $formValidation->createView(),
            'formObservation'         => $formObservation->createView(),
            'observations'            => $observations,
            'fichiers'                => $fichiers,
            'numDa'                   => $numDa,
            'connectedUser'           => $this->userDataService->getUser(),
            'statutAutoriserModifAte' => $da->getStatutDal() === StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
            'estCreateurDaDirecte'    => $this->estCreateurDaDirecte(),
            'estAppro'                => $this->estAppro(),
            'nePeutPasModifier'       => $this->nePeutPasModifier($da),
            'propValTemplate'         => 'proposition-validation-direct',
            'dossierJS'               => 'propositionDirect',
        ]);
    }

    private function nePeutPasModifier(DemandeAppro $demandeAppro)
    {
        return ($this->estCreateurDaDirecte() && ($demandeAppro->getStatutDal() == StatutDaConstant::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == StatutDaConstant::STATUT_VALIDE));
    }

    private function traitementFormulaire($form, $formObservation, Request $request, DemandeAppro $da)
    {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();
            $statutChange = $form->get('statutChange')->getData();
            $refsString = $request->request->get('refs');
            $this->demarrerSuiviProposition($request, $da->getNumeroDemandeAppro());

            $notification = null;
            if ($request->request->has('brouillon')) {
                /** Enregistrer provisoirement */
                $notification = SuiviOperationService::suivre('Enregistrement du brouillon', function () use ($da, $dalrList, $observation, $refsString) {
                    return $this->daPropositionService->enregistrerBrouillon($da, $dalrList, $observation, $refsString);
                });
            } elseif ($request->request->has('enregistrer')) {
                /** Envoyer proposition */
                $notification = SuiviOperationService::suivre('Envoi de la proposition', function () use ($da, $dalrList, $observation, $refsString) {
                    return $this->daPropositionService->envoyerProposition($da, $dalrList, $observation, $refsString);
                });
            } elseif ($request->request->has('changement')) {
                /** Valider les articles par le demandeur */
                $notification = SuiviOperationService::suivre('Validation par le demandeur', function () use ($da, $dalrList, $observation, $refsString) {
                    return $this->daPropositionService->validerParDemandeur($da, $dalrList, $observation, $refsString);
                });
            } elseif ($request->request->has('observation')) {
                /** Envoyer observation */
                $notification = SuiviOperationService::suivre('Envoi de l\'observation', function () use ($da, $observation, $statutChange) {
                    return $this->daPropositionService->envoyerObservation($da, $observation, $statutChange, $this->estAppro());
                });
            } elseif ($request->request->has('valider')) {
                /** Valider les articles par l'appro */
                $notification = SuiviOperationService::suivre('Validation par l\'appro', function () use ($da, $dalrList, $observation, $refsString) {
                    return $this->daPropositionService->validerParAppro($da, $dalrList, $observation, $refsString);
                });
            }

            if ($notification) $this->notifierEtRediriger($notification);
        }

        $formObservation->handleRequest($request);

        if ($formObservation->isSubmitted() && $formObservation->isValid()) {
            /** @var DaObservation $daObservation daObservation correspondant au donnée du formObservation */
            $daObservation = $formObservation->getData();

            SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_OBSERVATION, $da->getNumeroDemandeAppro());
            $this->notifierEtRediriger(SuiviOperationService::suivre('Observation avec pièces jointes', function () use ($da, $daObservation) {
                return $this->daPropositionService->envoyerObservationAvecPj($da, $daObservation, $this->estAppro());
            }));
        }
    }

    private function notifierEtRediriger(array $notification): void
    {
        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }

    /**
     * Démarre le suivi de l'opération (admin > historique > suivi des opérations) selon le bouton cliqué
     */
    private function demarrerSuiviProposition(Request $request, string $numDa): void
    {
        $types = [
            'brouillon'   => SuiviOperationService::TYPE_DA_PROPOSITION_BROUILLON,
            'enregistrer' => SuiviOperationService::TYPE_DA_PROPOSITION,
            'changement'  => SuiviOperationService::TYPE_DA_VALIDATION_CHOIX,
            'observation' => SuiviOperationService::TYPE_DA_OBSERVATION,
            'valider'     => SuiviOperationService::TYPE_DA_VALIDATION_APPRO,
        ];
        foreach ($types as $bouton => $type) {
            if ($request->request->has($bouton)) {
                SuiviOperationService::demarrer($type, $numDa);
                return;
            }
        }
    }
}
