<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Controller\Traits\magasin\devis\DevisMagasinTrait;
use App\Form\magasin\devis\DevisMagasinEnvoyerAuClientType;
use App\Service\magasin\devis\DevisMagasinEnvoyerAuClientValidatorService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinEnvoyerAuClientController extends Controller
{
    use AutorisationTrait;
    use DevisMagasinTrait;

    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private DevisMagasinRepository $devisMagasinRepository;
    private ListeDevisMagasinModel $listeDevisMagasinModel;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
    }

    /**
     * @Route("/devis-magasin-envoyer-au-client/{numeroDevis}", name="devis_magasin_envoyer_au_client")
     */
    public function envoyerAuClient(Request $request, string $numeroDevis)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_DVM);

        /** Gestion de blocage */
        $this->gestionDeBlocage($numeroDevis);

        //formulaire de création
        $form = $this->getFormFactory()->createBuilder(DevisMagasinEnvoyerAuClientType::class, null, [
            'data' => [
                'numeroDevis' => $numeroDevis
            ]
        ])->getForm();

        /** Traitement du formulaire */
        $this->traitementFormulaire($form, $request, $numeroDevis);

        //affichage du formulaire
        return $this->render('magasin/devis/envoyerAuClient.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function gestionDeBlocage(string $numeroDevis): Response
    {
        $validateur = new DevisMagasinEnvoyerAuClientValidatorService();

        //recupération des informations utile dans IPS
        $firstDevisIps = $this->getInfoDevisIps($numeroDevis);
        [$newSumOfLines, $newSumOfMontant] = $this->newSumOfLinesAndAmount($firstDevisIps);

        $data = [
            'numeroDevis' => $numeroDevis,
            'devisMagasinRepository' => $this->getEntityManager()->getRepository(DevisMagasin::class),
            'newSumOfLines' => $newSumOfLines,
            'newSumOfMontant' => $newSumOfMontant
        ];
        if (!$validateur->validateData($data)) {
            return $this->redirectToRoute($validateur->getRedirectRoute());
        }
        return new Response();
    }

    private function traitementFormulaire(FormInterface $form, Request $request, string $numeroDevis)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $numeroVersionMax = $this->getEntityManager()->getRepository(DevisMagasin::class)->getNumeroVersionMax($numeroDevis);
            $devisMagasin = $this->getEntityManager()->getRepository(DevisMagasin::class)->findOneBy(['numeroDevis' => $numeroDevis, 'numeroVersion' => $numeroVersionMax]);
            $devisMagasin->setDateEnvoiDevisAuClient($data['dateEnvoiDevisAuClient']);
            $devisMagasin->setStatutDw(DevisMagasin::STATUT_ENVOYER_CLIENT);
            $devisMagasin->setStatutBc(BcMagasin::STATUT_EN_ATTENTE_BC);
            $devisMagasin->setDatePointage(new \DateTime());
            $this->getEntityManager()->persist($devisMagasin);
            $this->getEntityManager()->flush();

            //HISTORISATION DE L'OPERATION
            $message = "Pointage enregistré avec succès .";
            $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');
            $nomDeRoute = 'devis_magasin_liste'; // route de redirection après soumission
            $nomInputSearch = 'devis_magasin_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $numeroDevis, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }
}
