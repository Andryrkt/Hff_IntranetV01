<?php

namespace App\Controller\dit;

use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Form\dit\DitValidationType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitValidationController extends Controller
{

    /**
     * @Route("/ditValidation/{id<\d+>}/{numDit<\w+>}", name="dit_validationDit")
     *
     * @return void
     */
    public function validationDit($numDit, $id, Request $request)
    {
        $autoriser = $this->estAdmin();

        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($id);
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

        $form = $this->getFormFactory()->createBuilder(DitValidationType::class, $dit)->getForm();

        //RECUPERATION DE LISTE COMMANDE 
        $commandes = $ditModel->RecupereCommandeOr($dit->getNumeroOR());

        $this->logUserVisit('dit_validationDit', [
            'id'     => $id,
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur       

        return  $this->render('dit/validation.html.twig', [
            'form' => $form->createView(),
            'dit' => $dit,
            'autoriser' => $autoriser,
            'commandes' => $commandes
        ]);
    }
}
