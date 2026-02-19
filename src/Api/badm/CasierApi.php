<?php

namespace App\Api\badm;

use App\Controller\Controller;
use App\Entity\cas\CasierValider;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\badm\BadmModel;

class CasierApi extends Controller
{
    private $badm;

    public function __construct()
    {
        parent::__construct();
        $this->badm = new BadmModel();
    }

    /**
     * @Route("/casierDestinataire", name="badm_casierDestinataire")
     */
    public function casierDestinataire()
    {
        $casierDestinataireInformix = $this->badm->recupeCasierDestinataireInformix();
        //$casierDestinataireSqlServer = $this->badm->recupeCasierDestinataireSqlServer();
        $casierDestinataire = $this->getEntityManager()->getRepository(CasierValider::class)->findAll();

        $casierDestinataireSqlServer = [];
        foreach ($casierDestinataire as $value) {
            $casierDestinataireSqlServer[] = [
                'Agence_Rattacher' => $value->getAgenceRattacher()->getCodeAgence(),
                'Casier' => $value->getCasier()
            ];
        }

        // Combinaison des deux tableaux
        $resultat = [];

        foreach ($casierDestinataireInformix as $agence) {
            foreach ($casierDestinataireSqlServer as $casier) {

                if ($casier['Agence_Rattacher'] == $agence['code_agence']) {

                    $resultat[$agence['agence']][] = $casier['Casier'];
                }
            }

            //Assurez-vous que chaque agence est présente même si elle n'a pas de casiers
            if (!array_key_exists($agence['agence'], $resultat)) {
                $resultat[$agence['agence']] = [];
            }
        }

        header("Content-type:application/json");

        $jsonData = json_encode($resultat);

        $this->testJson($jsonData);
    }
}
