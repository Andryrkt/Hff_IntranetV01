<?php

namespace App\Controller\rental;

use App\Controller\Controller;
use App\Constants\iframe\IframeConstant;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class rentalRolloutController extends Controller
{
    /**
     * @Route("/rental-rollout", name="rental_rollout")
     */
    public function rentalRollout()
    {
        return $this->render("iframe/iframe.html.twig", [
            'url'       => IframeConstant::LINK["rental-rollout"],
            'pageTitle' => "ROLLOUT RENTAL",
        ]);
    }
}
