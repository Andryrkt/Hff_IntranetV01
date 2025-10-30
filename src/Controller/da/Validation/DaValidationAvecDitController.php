<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;

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

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $this->validerDemandeApproAvecLignes($numDa, $numeroVersionMax, $prixUnitaire, $refsValide);

        /** CREATION EXCEL */
        $resultatExport = $this->exporterDaAvecDitEnExcelEtPdf($numDa, $numeroVersionMax);

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($resultatExport['fileName']);

        $this->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro(), true); // enregistrer dans la table Da Afficher

        $this->emailDaService->envoyerMailValidationDa($da, $this->getUser(), $resultatExport);

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da");
    }
}
