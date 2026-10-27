<?php

namespace App\Api\da;

use App\Controller\Controller;
use App\Service\da\DaAfficherService;
use App\Constants\da\StatutOrConstant;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/demande-appro")
 */
class DaAfficherApi extends Controller
{
    private DaAfficherService $daAfficherService;

    public function __construct(DaAfficherService $daAfficherService)
    {
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/generer-da-afficher", name="api_da_generate_daf")
     */
    public function genererDaAffichers()
    {
        $allDas = ['DAL25050049', 'DAL25060263', 'DAL25060166', 'DAL25070378', 'DAL25080060', 'DAL25080073', 'DAL25080186', 'DAL25090096', 'DAL25090116', 'DAL25090126', 'DAL25090189', 'DAL25090208', 'DAL25100244', 'DAL25100113', 'DAL25100060', 'DAL25100084', 'DAL25100096', 'DAL25090269', 'DAL25100112', 'DAL25100140', 'DAL25100119', 'DAL25100171', 'DAL25100173', 'DAL25100175', 'DAL25100141', 'DAL25100172', 'DAL25100228', 'DAL25100232'];
        foreach ($allDas as $numDa) {
            dump("numeroDemandeAppro = " . $numDa);
            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa, true, StatutOrConstant::STATUT_VALIDE);
            dump("Génération de Da Afficher réussie pour " . $numDa);
        }
        //dd('Génération réussie pour tous les DAs');
    }
}
