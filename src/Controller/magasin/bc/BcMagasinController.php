<?php

namespace App\Controller\magasin\bc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Form\magasin\bc\BcMagasinType;
use App\Model\magasin\bc\BcMagasinModel;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class BcMagasinController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/soumission-bc-magasin/{numeroDevis}", name="bc_magasin_soumission", defaults={"numeroDevis"=null})
     */
    public function index(?string $numeroDevis = null, Request $request): Response
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        $bcMagasinDto = new \App\Model\magasin\bc\BcMagasinDto();
        $bcMagasinDto->numeroDevis = $numeroDevis;

        // recuperatino de l'info du devis
        $bcMagasinModel = new BcMagasinModel();
        $infoDevis = $bcMagasinModel->getInformaitonDevisMagasin($numeroDevis);

        foreach ($infoDevis as $ligneData) {
            $ligneDto = new \App\Model\magasin\bc\BcMagasinLigneDto();
            $ligneDto->numeroLigne = $ligneData['numero_ligne'];
            $ligneDto->constructeur = $ligneData['constructeur'];
            $ligneDto->ref = $ligneData['ref'];
            $ligneDto->designation = $ligneData['designation'];
            $ligneDto->qte = $ligneData['qte'];
            $ligneDto->prixHt = $ligneData['prix_ht'];
            $ligneDto->montantNet = $ligneData['montant_net'];
            $ligneDto->remise1 = $ligneData['remise1'];
            $ligneDto->remise2 = $ligneData['remise2'];
            $bcMagasinDto->lignes[] = $ligneDto;
        }

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcMagasinType::class, $bcMagasinDto)->getForm();

        //tratiement formulaire
        $this->tratitementFormulaire($form, $request);

        //affichage du formulaire
        return $this->render('magasin/bc/soumission.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function tratitementFormulaire($form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: traiter et enregistrer les données du formulaire
        }
    }
}
