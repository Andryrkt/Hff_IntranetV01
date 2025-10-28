<?php

namespace App\Controller\da\Validation;

use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\validation\DaValidationReapproTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\da\DemandeAppro;

/**
 * @Route("/demande-appro")
 */
class DaValidationReapproController extends Controller
{
    use DaAfficherTrait;
    use DaValidationReapproTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationReapproTrait();
    }

    /**
     * @Route("/validation/{numDa}", name="da_validate_reappro")
     */
    public function validationDaReappro(string $numDa, Request $request)
    {

        return $this->render("da/validation-reappro.html.twig", [
            'da'                      => $da,
            'id'                      => $id,
            'form'                    => $form->createView(),
            'formValidation'          => $formValidation->createView(),
            'formObservation'         => $formObservation->createView(),
            'observations'            => $observations,
            'numDa'                   => $numDa,
            'connectedUser'           => $this->getUser(),
            'statutAutoriserModifAte' => $da->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            'estCreateurDaDirecte'    => $this->estCreateurDeDADirecte(),
            'estAppro'                => $this->estUserDansServiceAppro(),
            'nePeutPasModifier'       => $this->nePeutPasModifier($da),
            'propValTemplate'         => 'proposition-validation-direct',
            'dossierJS'               => 'propositionDirect',
        ]);
    }
}
