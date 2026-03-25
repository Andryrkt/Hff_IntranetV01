<?php

namespace App\Controller\magasin\devis\Pointage;

use App\Controller\Controller;
use App\Factory\magasin\devis\Pointage\EnvoyerAuClientFactory;
use App\Form\magasin\devis\Pointage\EnvoyerAuClientType;
use App\Model\magasin\devis\Pointage\PointageModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class EnvoyerAuClientController extends Controller
{
    /**
     * @Route("/pointage/envoyer-au-client/{numeroDevis}", name="pointage_envoyer_au_client")
     */
    public function index($numeroDevis)
    {
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $dto = (new EnvoyerAuClientFactory())->create($numeroDevis, $codeSociete);

        //formulaire de création
        $form = $this->getFormFactory()->createBuilder(EnvoyerAuClientType::class, $dto)->getForm();

        //affichage du formulaire
        return $this->render('magasin/devis/pointage/EnvoyerAuClient/envoyer_au_client.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request, $dto)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();



            // Enregistrement dans la base de données
            $pointageModel = new PointageModel();
            $pointageModel->updatePointage($dto);

            //HISTORISATION DE L'OPERATION
            $message = "Pointage enregistré avec succès .";
            $criteria = (array) ($this->getSessionService()->get('criteria_for_excel_liste_devis_neg') ?? []);
            $nomDeRoute = 'liste_devis_neg'; // route de redirection après soumission
            $nomInputSearch = 'devis_neg_search'; // initialistion de nom de chaque champ ou input
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $dto->numeroDevis, $nomDeRoute, true, $criteria, $nomInputSearch);
        }
    }
}
