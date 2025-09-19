<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationDirectTrait;

/**
 * @Route("/demande-appro")
 */
class DaValidationDirectController extends Controller
{
    use DaAfficherTrait;
    use DaValidationDirectTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationDirectTrait();
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

        $this->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro(), true); // enregistrer dans la table Da Afficher

        // ajout des données dans la table DaSoumisAValidation
        $this->ajouterDansDaSoumisAValidation($da);

        /** envoi dans docuware */
        $this->copyToDW($da);

        /** ENVOIE D'EMAIL */
        $this->emailDaService->envoyerMailValidationDaDirect($da, $resultatExport, [
            'service'           => 'appro',
            'phraseValidation'  => 'Vous trouverez en pièce jointe le fichier contenant les références ZDI.',
            'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da");
    }
}
