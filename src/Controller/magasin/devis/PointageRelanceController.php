<?php

namespace App\Controller\magasin\devis;

use App\Api\magasin\AutocompletionApi;
use App\Controller\Controller;
use App\Entity\magasin\devis\DevisMagasin;
use App\Dto\Magasin\Devis\PointageRelanceDto;
use App\Entity\magasin\devis\PointageRelance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\magasin\devis\PointageRelanceType;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\magasin\devis\PointageRelanceFactory;
use App\Service\autres\AutoIncDecService;

/**
 * @Route("/magasin/dematerialisation")
 */
class PointageRelanceController extends Controller
{
    /**
     * Affiche le formulaire de pointage relance dans un modal (appel AJAX)
     * @Route("/pointage-relance-form/{numeroDevis}", name="devis_magasin_relance_client_form")
     */
    public function pointageRelanceForm(?string $numeroDevis = null): Response
    {
        $dto = (new PointageRelanceFactory)->create($numeroDevis);
        $form = $this->getFormFactory()->createNamed('', PointageRelanceType::class, $dto, [
            'action' => $this->getUrlGenerator()->generate('devis_magasin_relance_client_submit')
        ]);

        return $this->render('magasin/devis/pointage_relance/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Traite la soumission du formulaire de pointage relance
     * @Route("/pointage-relance-submit", name="devis_magasin_relance_client_submit", methods={"POST"})
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
            $pointageRelanceEntity = (new PointageRelanceFactory())->map($data, $this->getUserName(), $this->numeroRelance($data['numeroDevis']));
            $this->getEntityManager()->persist($pointageRelanceEntity);
            $this->getEntityManager()->flush();

            // Mettre à jour le statut de relance du devis
            $this->modifictionTableDevisSoumisAValidationNeg($pointageRelanceEntity);

            return $this->jsonResponse(['success' => true, 'message' => 'Formulaire soumis avec succès.']);
        }

        // Si le formulaire n'est pas valide, renvoyer les erreurs.
        return $this->jsonResponse(['success' => false, 'message' => 'Erreurs de validation.', 'errors' => (string) $form->getErrors(true, false)], 400);
    }

    public function numeroRelance(int $numeroDevis): int
    {
        $numeroRelanceMax = $this->getEntityManager()->getRepository(PointageRelance::class)->getNumeroRelanceMax($numeroDevis);
        return AutoIncDecService::autoIncrement($numeroRelanceMax);
    }

    private function modifictionTableDevisSoumisAValidationNeg(PointageRelance $pointageRelanceEntity): void
    {
        $devis = $this->getEntityManager()->getRepository(DevisMagasin::class)->getDevis($pointageRelanceEntity->getNumeroDevis());
        $devis->setStatutRelance('Relancé');
        $this->getEntityManager()->flush();
    }
}
