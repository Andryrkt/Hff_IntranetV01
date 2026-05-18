<?php

namespace App\Controller\magasin\devis;

use App\Api\magasin\AutocompletionApi;
use App\Controller\Controller;
use App\Dto\Magasin\Devis\PointageRelanceDto;
use App\Entity\magasin\devis\DevisMagasin;
use App\Entity\magasin\devis\PointageRelance;
use App\Factory\magasin\devis\PointageRelanceFactory;
use App\Form\magasin\devis\PointageRelanceType;
use App\Model\magasin\devis\Pointage\PointageRelanceModel;
use App\Service\autres\AutoIncDecService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class PointageRelanceController extends Controller
{
    /**
     * Affiche le formulaire de pointage relance dans un modal (appel AJAX)
     * @Route("/pointage-relance-form/{numeroDevis}", name="api_devis_magasin_relance_client_form")
     */
    public function pointageRelanceForm(?string $numeroDevis = null): Response
    {
        $dto = (new PointageRelanceFactory)->create($numeroDevis);
        $form = $this->getFormFactory()->createNamed('', PointageRelanceType::class, $dto, [
            'action' => $this->getUrlGenerator()->generate('api_devis_magasin_relance_client_submit')
        ]);

        return $this->render('magasin/devis/pointage_relance/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Traite la soumission du formulaire de pointage relance
     * @Route("/pointage-relance-submit", name="api_devis_magasin_relance_client_submit", methods={"POST"})
     */
    public function submitPointageRelanceForm(Request $request): Response
    {
        $dto = new PointageRelanceDto();
        $form = $this->getFormFactory()->createNamed('', PointageRelanceType::class, $dto);

        // handleRequest est pour les données de formulaire standard (x-www-form-urlencoded)
        // Pour les requêtes JSON, nous devons décoder le contenu et le soumettre au formulaire.
        $data = json_decode($request->getContent(), true);

        // Si json_decode échoue ou si les données sont vides, renvoyer une erreur
        if ($data === null) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid JSON payload.'], 400);
        }

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Code Société de l'utilisateur
            $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

            $pointageRelanceEntity = (new PointageRelanceFactory())->map($data, $this->getUserName(), $this->numeroRelance($data['numeroDevis'], $codeSociete), $codeSociete);
            $pointageRelanceModel = new PointageRelanceModel();
            $pointageRelanceModel->enregistrerPointageRelance($pointageRelanceEntity);

            // Mettre à jour le statut de relance du devis
            $numeroVersionDevis = $pointageRelanceModel->getNumeroVersionDevis($pointageRelanceEntity->getNumeroDevis(), $codeSociete);
            $pointageRelanceModel->updateDevis($pointageRelanceEntity, $numeroVersionDevis);


            return $this->jsonResponse(['success' => true, 'message' => 'Formulaire soumis avec succès.']);
        }

        // Si le formulaire n'est pas valide, renvoyer les erreurs.
        return $this->jsonResponse(['success' => false, 'message' => 'Erreurs de validation.', 'errors' => (string) $form->getErrors(true, false)], 400);
    }

    /**
     * Génére le numéro de relance
     *
     * @param int $numeroDevis
     * @param string $codeSociete
     * @return int
     */
    private function numeroRelance(int $numeroDevis, string $codeSociete): int
    {
        $pointageRelanceModel = new PointageRelanceModel();
        $numeroRelanceMax = $pointageRelanceModel->getDernierNumeroRelance($numeroDevis, $codeSociete);
        return AutoIncDecService::autoIncrement($numeroRelanceMax);
    }
}
