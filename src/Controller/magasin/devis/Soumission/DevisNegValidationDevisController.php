<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Factory\magasin\devis\Soumission\ValidationDevisFactory;
use App\Form\magasin\devis\Soumission\ValidationDevisType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegValidationDevisController extends Controller
{
    /**
     * @Route("/soumission-devis-neg-validation-devis/{typeSoumission}/{numeroDevis}", name="devis_neg_soumission_validation_devis", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $typeSoumission = null, ?string $numeroDevis = null, Request $request)
    {
        $codeSociette = $this->getSecurityService()->getCodeSocieteUser();

        // Création du DTO à partir des paramètres de la requête
        $dto = ValidationDevisFactory::create($typeSoumission, $numeroDevis, $codeSociette);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(ValidationDevisType::class, $dto, [
            "fichier_initialise" => (bool)$dto->remoteUrlCourt, // Indique si un fichier existe déjà pour ce devis
        ])->getForm();

        // traitement du formulaire
        // $this->traitementFormulaire($form, $request);

        return $this->render('magasin/devis/soumission/validation_devis.html.twig', [
            'form'        => $form->createView(),
            'remoteUrl'   => $dto->remoteUrlCourt,
        ]);
    }
}
