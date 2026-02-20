<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitDevisSoumisAValidation;
use App\Model\dit\DitOrSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;

class DocSoumisDwApi extends Controller
{
    private $ditOrsoumisAValidationModel;

    public function __construct()
    {
        parent::__construct();

        $this->ditOrsoumisAValidationModel = new DitOrSoumisAValidationModel();
    }
    /**
     * @Route("/api/constraint-soumission/{numDit}", name="api_constraint_soumission")
     *
     * @param string $numDit
     * @return void
     */
    public function constraintSoumission($numDit)
    {
        $constraitSoumission = $this->recupConstrainte($numDit);

        header("Content-type:application/json");

        echo json_encode($constraitSoumission);
    }

    private function recupConstrainte(string $numDit): array
    {
        $constraitDevis = $this->getEntityManager()->getRepository(DemandeIntervention::class)->recupConstraitSoumission($numDit);

        $statutDevis = $this->getEntityManager()->getRepository(DitDevisSoumisAValidation::class)->findStatutDevis($numDit);

        $numOrBaseDonner = $this->ditOrsoumisAValidationModel->recupNumeroOr($numDit);


        if (empty($constraitDevis)) {
            $client = "";
            $statutDit = "";
        } else {
            $client = $constraitDevis[0]['client'];
            $statutDit = $constraitDevis[0]['statut'];
        }

        if (empty($numOrBaseDonner)) {
            $numeroOR = '';
        } else {
            $numeroOR = $numOrBaseDonner[0]['numor'];
        }

        return  [
            "client" => $client,
            "statutDit" => $statutDit,
            "statutDevis" => $statutDevis,
            "numeroOR" => $numeroOR
        ];
    }
}
