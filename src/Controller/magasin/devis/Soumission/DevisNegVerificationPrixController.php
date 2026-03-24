<?php

namespace App\Controller\magasin\devis\Soumission;

use App\Controller\Controller;
use App\Form\magasin\devis\Soumission\VerificationPrixType;
use App\Mapper\Magasin\Devis\Soumission\SoumissionMapper;
use App\Service\magasin\devis\Validator\DevisMagasinValidationVpOrchestrator;
use DirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisNegVerificationPrixController extends Controller
{
    /**
     * @Route("/soumission-devis-neg-verification-de-prix/{typeSoumission}/{numeroDevis}", name="devis_neg_soumission_verification_prix", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $typeSoumission = null, ?string $numeroDevis = null, Request $request)
    {
        $dto = SoumissionMapper::toDto($typeSoumission, $numeroDevis, $this->getSecurityService());

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(VerificationPrixType::class, $dto, [
            "fichier_initialise" => (bool)$dto->remoteUrlCourt, // Indique si un fichier existe déjà pour ce devis
        ])->getForm();

        return $this->render('magasin/devis/soumissionVerificationPrix.html.twig', [
            'form'        => $form->createView(),
            'remoteUrl'   => $dto->remoteUrlCourt,
        ]);
    }
}
