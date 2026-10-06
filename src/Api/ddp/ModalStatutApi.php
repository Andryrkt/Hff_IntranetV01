<?php

namespace App\Api\ddp;

use App\Controller\Controller;
use App\Entity\ddp\HistoriqueStatutDdp;
use App\Repository\ddp\HistoriqueStatutDdpRepository;
use Symfony\Component\Routing\Annotation\Route;

class ModalStatutApi extends Controller
{
    private HistoriqueStatutDdpRepository $historiqueStatutDdpRepository;

    public function __construct()
    {
        parent::__construct();

        $this->historiqueStatutDdpRepository = $this->getEntityManager()->getRepository(HistoriqueStatutDdp::class);
    }

    /**
     * @Route("/ddp/api/historique-statut/{numDdp}", name="api_ddp_historique_statut", methods={"GET"})
     *
     * @return void
     */
    public function historiqueStatut($numDdp)
    {
        $statuts = $this->historiqueStatutDdpRepository->getHistoriqueStatut($numDdp);

        $result = [];
        foreach ($statuts as $statut) {
            $result[] = [
                'id' => $statut->getId(),
                'numeroDdp' => $statut->getNumeroDdp(),
                'statut' => $statut->getStatut(),
                'date' => $statut->getDate()->format('Y-m-d H:i:s'),
            ];
        }

        header("Content-type:application/json");

        echo json_encode($result);
    }
}
