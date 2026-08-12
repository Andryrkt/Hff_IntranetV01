<?php

namespace App\Controller\rental;

use App\Controller\Controller;
use App\Constants\iframe\IframeConstant;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel")
 */
class rentalRolloutController extends Controller
{
    /**
     * @Route("/dashboard/alerte-materiel-location", name="alerte_materiel_location")
     */
    public function rentalRollout()
    {
        return $this->render("iframe/iframe.html.twig", [
            'url'       => IframeConstant::LINK["rental-rollout"],
            'pageTitle' => "Alertes Materiels de location",
        ]);
    }
}
