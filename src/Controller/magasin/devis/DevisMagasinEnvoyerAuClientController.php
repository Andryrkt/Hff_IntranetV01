<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\devis\DevisMagasinEnvoyerAuClientType;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinEnvoyerAuClientController extends Controller
{
    use AutorisationTrait;

    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperationDeviMagasinService = new HistoriqueOperationDevisMagasinService();
    }

    /**
     * @Route("/devis-magasin-envoyer-au-client/{numeroDevis}", name="devis_magasin_envoyer_au_client")
     */
    public function envoyerAuClient(Request $request, string $numeroDevis)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //formulaire de création
        $form = $this->getFormFactory()->createBuilder(DevisMagasinEnvoyerAuClientType::class, null, [
            'data' => [
                'numeroDevis' => $numeroDevis
            ]
        ])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $numeroVersionMax = $this->getEntityManager()->getRepository(DevisMagasin::class)->getNumeroVersionMax($numeroDevis);
            $devisMagasin = $this->getEntityManager()->getRepository(DevisMagasin::class)->findOneBy(['numeroDevis' => $numeroDevis, 'numeroVersion' => $numeroVersionMax]);
            $devisMagasin->setDateEnvoiDevisAuClient($data['dateEnvoiDevisAuClient']);
            $this->getEntityManager()->persist($devisMagasin);
            $this->getEntityManager()->flush();

            //HISTORISATION DE L'OPERATION
            $message = "le devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyé au client avec succès .";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', true);
        }

        //affichage du formulaire
        return $this->render('magasin/devis/envoyerAuClient.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
