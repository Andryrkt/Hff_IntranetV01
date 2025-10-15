<?php

namespace App\Controller\magasin\bc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Form\magasin\bc\BcMagasinType;
use App\Model\magasin\bc\BcMagasinDto;
use App\Model\magasin\bc\BcMagasinModel;
use App\Controller\Traits\AutorisationTrait;
use App\Factory\magasin\bc\BcMagasinFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Factory\magasin\bc\BcMagasinDtoFactory;
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

        $factory = new BcMagasinDtoFactory();
        $bcMagasinDto = $factory->create($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BcMagasinType::class, $bcMagasinDto)->getForm();

        //tratiement formulaire
        $this->tratitementFormulaire($form, $request);

        //affichage du formulaire
        return $this->render('magasin/bc/soumission.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function tratitementFormulaire($form, Request $request, ?string $numeroDevis = null): void
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BcMagasinDto $dto */
            $dto = $form->getData();

            // recupération montant devis
            $bcMagasinModel = new BcMagasinModel();
            $montantDevis  = $bcMagasinModel->getMontantDevis($numeroDevis)[0] ?? 0.00;

            // Utiliser la factory pour créer l'entité à partir du DTO
            $this->enregistrementDonnees($dto, (float) $montantDevis);

            // TODO: creation de page de garde

            // TODO: gestion des pieces jointes

            // TODO: fusion du page de garde et des pieces jointes (mettre la page de garde en derniere page)

            // TODO: envoie du pdf fusion dans DW

            //TODO: historique du document

        }
    }

    private function enregistrementDonnees(BcMagasinDto $dto, ?float $montantDevis): void
    {
        $entityManager = $this->getEntityManager();

        $factory = new BcMagasinFactory();
        $bcMagasin = $factory->createFromDto($dto, $this->getUser(), $entityManager, $montantDevis);

        $entityManager->persist($bcMagasin);
        $entityManager->flush();
    }

    private function mettreLesDonnerDansEntite(BcMagasinDto $dto): BcMagasin
    {
        $bcMagasin = new BcMagasin();

        return $bcMagasin->setNumeroDevis($dto->numeroDevis)
            ->setNumeroBc($dto->numeroBc)
            ->setMontantDevis(0.00)
            ->setMontantBc($this->modificationEnFloat($dto->montantBc))
            ->setNumeroVersion(1)
            ->setStatutBc('Soumis à validation')
            ->setObservation($dto->observation)
            ->setUtilisateur($this->getUser()->getNomUtilisateur());
    }

    private function modificationEnFloat(string $montant): float
    {
        $montant = str_replace([' ', ','], ['', '.'], $montant);
        return (float) $montant;
    }
}
