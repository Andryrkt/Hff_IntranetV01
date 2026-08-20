<?php

namespace App\Controller\dit;

use App\Model\dit\DitModel;
use App\Dto\Dit\DitDetailDto;
use App\Controller\Controller;
use App\Service\Admin\UrlIdCipher;
use App\Entity\dit\DitObservation;
use App\Form\dit\DitObservationType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitDetailController extends Controller
{
    /**
     * @Route("/fiche-detail-dit/{token}", name="dit_fiche_detail")
     */
    public function detailDit(string $token, Request $request)
    {
        $realId = (new UrlIdCipher)->decryptInt($token);

        if (empty($realId) && $realId !== 0) throw new ResourceNotFoundException();

        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($realId);
        $ditModel = new DitModel();
        $data = $ditModel->findAll($dit->getIdMateriel(), $dit->getNumParc(), $dit->getNumSerie());

        $dit->setNumParc($data[0]['num_parc']);
        $dit->setNumSerie($data[0]['num_serie']);
        $dit->setIdMateriel($data[0]['num_matricule']);
        $dit->setConstructeur($data[0]['constructeur']);
        $dit->setModele($data[0]['modele']);
        $dit->setDesignation($data[0]['designation']);
        $dit->setCasier($data[0]['casier_emetteur']);
        //Bilan financière
        $dit->setCoutAcquisition($data[0]['prix_achat']);
        $dit->setAmortissement($data[0]['amortissement']);
        $dit->setChiffreAffaire($data[0]['chiffreaffaires']);
        $dit->setChargeEntretient($data[0]['chargeentretien']);
        $dit->setChargeLocative($data[0]['chargelocative']);
        $dit->setResultatExploitation($data[0]['chiffreaffaires'] - ($data[0]['chargeentretien'] + $data[0]['chargelocative']));
        $dit->setValeurNetComptable($data[0]['prix_achat'] - $data[0]['amortissement']);
        //Etat machine
        $dit->setKm($data[0]['km']);
        $dit->setHeure($data[0]['heure']);

        if ($dit->getInternetExterne() === 'I') {
            $dit->setInternetExterne('INTERNE');
        } elseif ($dit->getInternetExterne() === 'E') {
            $dit->setInternetExterne('EXTERNE');
        }

        $form = $this->getFormFactory()->createBuilder(DitObservationType::class, new DitObservation)->getForm();

        //RECUPERATION DE LISTE COMMANDE 
        $commandes = $ditModel->RecupereCommandeOr($dit->getNumeroOR());

        $this->logUserVisit('dit_fiche_detail', ['id' => $realId]); // historisation du page visité par l'utilisateur

        return  $this->render('dit/detail.html.twig', [
            'form' => $form->createView(),
            'dit'  => DitDetailDto::fromEntity($dit, $commandes)
        ]);
    }
}
