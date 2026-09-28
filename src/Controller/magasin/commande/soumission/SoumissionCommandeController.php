<?php

namespace App\Controller\magasin\commande\soumission;

use App\Controller\Controller;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\genererPdf\magasin\cdeFrn\GeneratePdfCdeMagasin;
use App\Service\historiqueOperation\HistoriqueOperationCDEFNRService;
use App\Form\magasin\Commande\SoumissionCommande\SoumissionCommandeType;

/**
 * @Route("/magasin/commande")
 */
class SoumissionCommandeController extends Controller
{
    private GeneratePdfCdeMagasin $generatePdfCdeMagasin;
    private HistoriqueOperationCDEFNRService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->generatePdfCdeMagasin = new GeneratePdfCdeMagasin();
        $this->historiqueOperation = new HistoriqueOperationCDEFNRService($this->getEntityManager());
    }

    /**
     * @Route("/generer-commande-fournisseur", name="generer_commande_fournisseur")
     */
    public function soumissionCommande(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(SoumissionCommandeType::class, null, [
            'method' => 'POST',
        ])->getForm();

        $form->handleRequest($request);

        $this->logUserVisit('generer_commande_fournisseur');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->soumettreAValider($form);
        }

        return $this->render('magasin/commande/soumission/soumissionCommandeFournisseur.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function soumettreAValider(FormInterface $form)
    {
        try {
            $numCommande = $form->get('numCmdeAValider')->getData();
            $generatedFilePath = $form->get('generatedFilePath')->getData();

            $numVersion = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class)->findNumeroVersionMax($numCommande) ?? 0;

            $cdeSoumis = new CdefnrSoumisAValidation();
            $cdeSoumis
                ->setNumCdeFournisseur($numCommande)
                ->setNumVersion($numVersion + 1)
                ->setDateHeureSoumission(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))
                ->setStatut('Soumis à validation')
            ;

            $cheminDuFichier = $_ENV["BASE_PATH_FICHIER"] . str_replace('/Upload', '', $generatedFilePath);

            if (!file_exists($cheminDuFichier)) throw new \Exception("Le fichier PDF n'existe pas : " . $generatedFilePath);

            $isCopiedToDWFilePath = $this->generatePdfCdeMagasin->copyToDOCUWARE($cheminDuFichier, $numCommande);

            // if ($isCopiedToDWFilePath) $bcSoumisMagasinDto->deposerDw = true;

            $this->getEntityManager()->persist($cdeSoumis);
            $this->getEntityManager()->flush();

            $this->historiqueOperation->sendNotificationSoumission('Votre demande a été enregistrée', $numCommande, 'profil_acceuil', true);
        } catch (\Throwable $th) {
            $this->historiqueOperation->sendNotificationSoumission('Echec lors de la soumission:' . $th->getMessage(), $numCommande, 'profil_acceuil');
        }
    }
}
