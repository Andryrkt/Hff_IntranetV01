<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/demande-appro")
 */
class DaNewDirectApiController extends Controller
{
    /**
     * @Route("/new-direct", name="api_da_new_direct", methods={"POST"})
     */
    public function newDirect(Request $request) {}
}
