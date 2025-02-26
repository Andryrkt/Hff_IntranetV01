<?php

namespace App\Controller\Traits;

use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\mutation\Mutation;
use DateTime;

trait MutationTrait
{
    private function initialisationMutation(Mutation $mutation, $em)
    {
        $agenceServiceIps = $this->agenceServiceIpsObjet();

        $mutation
            ->setDateDemande(new DateTime())
            ->setDevis('MGA')
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument($em->getRepository(SousTypeDocument::class)->find(5)) // Sous-type document MUTATION
            ->setTypeDocument($mutation->getSousTypeDocument()->getCodeDocument())
        ;
    }

    private function enregistrementValeurDansMutation(Mutation $mutation)
    {
        # code...
    }
}
