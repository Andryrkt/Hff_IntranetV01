<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Service\da\DaAfficherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DemandeApproLRepository;
use App\Entity\da\DemandeApproL;
use App\Service\da\EmailDaService;
use App\Service\da\DaValidationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaValidationAvecDitController extends Controller
{
    private DemandeApproLRepository $demandeApproLRepository;
    private EmailDaService $emailDaService;
    private DaAfficherService $daAfficherService;
    private DaValidationService $daValidationService;

    public function __construct(
        EntityManagerInterface $em,
        DaAfficherService $daAfficherService,
        DaValidationService $daValidationService,
        EmailDaService $emailDaService
    ) {
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
        $this->emailDaService = $emailDaService;
        $this->daValidationService = $daValidationService;
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/validate-avec-dit/{numDa}", name="da_validate_avec_dit")
     */
    public function validate(string $numDa, Request $request)
    {
        $daValidationData = $request->request->get('da_proposition_validation');
        $refsValide = json_decode($daValidationData['refsValide'], true) ?? [];
        $prixUnitaire = $request->get('PU', []); // obtenir les PU envoyé par requête

        // suivi étape par étape de la validation (admin > historique > suivi des opérations)
        $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_VALIDATION_AVEC_DIT, $numDa);

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $suivi->etape('Validation des lignes (base de données)', function () use ($numDa, $numeroVersionMax, $prixUnitaire, $refsValide) {
            return $this->daValidationService->validerDemandeApproAvecLignes($numDa, $numeroVersionMax, $prixUnitaire, $refsValide);
        });

        /** CREATION EXCEL */
        $resultatExport = $suivi->etape('Bon d\'achat (Excel et PDF)', function () use ($numDa, $numeroVersionMax) {
            return $this->daValidationService->exporterDaAvecDitEnExcelEtPdf($numDa, $numeroVersionMax);
        });

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($resultatExport['fileName']);

        $suivi->etape('Mise à jour da_afficher', function () use ($da) {
            $this->daAfficherService->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro(), true); // enregistrer dans la table Da Afficher
        });

        $suivi->etape('Mail de validation', function () use ($da, $resultatExport) {
            $this->emailDaService->envoyerMailValidationDa($da, $resultatExport);
        });

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
