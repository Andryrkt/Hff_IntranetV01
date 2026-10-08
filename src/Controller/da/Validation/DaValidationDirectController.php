<?php

namespace App\Controller\da\Validation;

use App\Constants\da\StatutDaConstant;
use App\Controller\Controller;
use App\Service\da\DaAfficherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationDirectTrait;
use App\Entity\da\DemandeAppro;

/**
 * @Route("/demande-appro")
 */
class DaValidationDirectController extends Controller
{
    use DaValidationDirectTrait;

    private DaAfficherService $daAfficherService;

    public function __construct(DaAfficherService $daAfficherService)
    {
        $this->initDaValidationDirectTrait();
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/validate-direct/{numDa}", name="da_validate_direct")
     */
    public function validate(string $numDa, Request $request)
    {
        $daValidationData = $request->request->get('da_proposition_validation');
        $refsValide = json_decode($daValidationData['refsValide'], true) ?? [];
        $prixUnitaire = $request->get('PU', []); // obtenir les PU envoyé par requête

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $this->validerDemandeApproAvecLignes($numDa, $numeroVersionMax, $prixUnitaire, $refsValide);

        /** CREATION EXCEL ET PDF */
        $resultatExport = $this->exporterDaDirectEnExcelEtPdf($numDa, $numeroVersionMax);

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($resultatExport['fileName']);

        $this->daAfficherService->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro(), true, StatutDaConstant::STATUT_DW_A_VALIDE); // enregistrer dans la table Da Afficher

        // ajout des données dans la table DaSoumisAValidation
        $this->ajouterDansDaSoumisAValidation($da);

        /** envoi dans docuware */
        $this->fusionAndCopyToDW($da->getNumeroDemandeAppro());

        $this->emailDaService->envoyerMailValidationDa($da, $this->getUser(), $resultatExport);

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
