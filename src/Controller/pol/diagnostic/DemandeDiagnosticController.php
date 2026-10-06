<?php


namespace App\Controller\pol\diagnostic;

use App\Constants\dw\DwConstant;
use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol")
 */
class DemandeDiagnosticController extends Controller
{

    /**
     * @Route("/nouveau-demande-diagnostic", name="nouveau_demande_diagnostic")
     */
    public function index()
    {
        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => DwConstant::LINK["new-diagnostic"],
            'pageTitle' => "Nouvelle demande de diagnostic",
            'bgColor'   => "bg-blue-cat",
            'height'    => 1530,
        ]);
    }
}
