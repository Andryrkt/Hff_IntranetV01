<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitDevisSoumisAValidation;
use App\Model\dit\DitOrSoumisAValidationModel;
use App\Repository\dit\DitDevisSoumisAValidationRepository;
use App\Repository\dit\DitRepository;
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
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /** @var DitRepository $ditRepository */
        $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
        $constraitDevis = $ditRepository->recupConstraitSoumission($numDit, $codeSociete);

        /** @var DitDevisSoumisAValidationRepository $ditDevisRepository */
        $ditDevisRepository = $this->getEntityManager()->getRepository(DitDevisSoumisAValidation::class);
        $statutDevis = $ditDevisRepository->findStatutDevis($numDit, $codeSociete);

        $numOrBaseDonner = $this->ditOrsoumisAValidationModel->recupNumeroOr($numDit, $codeSociete);

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
