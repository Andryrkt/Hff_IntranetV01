<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * @Route("/demande-appro")
 */
class DaValidationAvecDitController extends Controller
{
    use DaAfficherTrait;
    use DaValidationAvecDitTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationAvecDitTrait();
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
            return $this->validerDemandeApproAvecLignes($numDa, $numeroVersionMax, $prixUnitaire, $refsValide);
        });

        /** CREATION EXCEL */
        $resultatExport = $suivi->etape('Bon d\'achat (Excel et PDF)', function () use ($numDa, $numeroVersionMax) {
            return $this->exporterDaAvecDitEnExcelEtPdf($numDa, $numeroVersionMax);
        });

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($resultatExport['fileName']);

        $suivi->etape('Mise à jour da_afficher', function () use ($da) {
            $this->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro(), true); // enregistrer dans la table Da Afficher
        });

        $suivi->etape('Mail de validation', function () use ($da, $resultatExport) {
            $this->emailDaService->envoyerMailValidationDa($da, $this->getUser(), $resultatExport);
        });

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
