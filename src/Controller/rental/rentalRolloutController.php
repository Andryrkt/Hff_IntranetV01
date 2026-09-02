<?php

namespace App\Controller\rental;

use App\Controller\Controller;
use App\Constants\iframe\IframeConstant;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel")
 */
class RentalRolloutController extends Controller
{
    /**
     * @Route("/dashboard/rental-rollout", name="rental_rollout")
     */
    public function rentalRollout()
    {
        return $this->render("iframe/iframe.html.twig", [
            'url'       => IframeConstant::LINK["rental-rollout"],
            'pageTitle' => "RENTAL Rollout",
        ]);
    }

    /**
     * @Route("/dashboard/ge-rollout", name="ge_rollout")
     */
    public function geRollout()
    {
        return $this->render("iframe/iframe.html.twig", [
            'url'       => IframeConstant::LINK["ge-rollout"],
            'pageTitle' => "GE Rollout",
        ]);
    }
}
