<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\da\soumissionBC\DaSoumissionBcType;
use App\Service\historiqueOperation\HistoriqueOperationService;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionBcController extends Controller
{
    const STATUT_SOUMISSION = 'Soumis à validation';

    private  DaSoumissionBc $daSoumissionBc;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private HistoriqueOperationService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();

        $this->daSoumissionBc = new DaSoumissionBc();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/soumissionBc';
        $this->historiqueOperation      = new HistoriqueOperationDaBcService();
    }

    /**
     * @Route("/soumission-bc/{numCde}", name="da_soumission_bc")
     */
    public function index(string $numCde, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->daSoumissionBc->setNumeroCde($numCde);

        $form = self::$validator->createBuilder(DaSoumissionBcType::class, $this->daSoumissionBc, [
            'method' => 'POST',
        ])->getForm();

        $this->traitementFormulaire($request, $numCde, $form);

        self::$twig->display('da/soumissionBc.html.twig', [
            'form' => $form->createView(),
            'numCde' => $numCde,
        ]);
    }

    /**
     * permet de faire le rtraitement du formulaire
     *
     * @param Request $request
     * @param string $numCde
     * @param [type] $form
     * @return void
     */
    private function traitementFormulaire(Request $request, string $numCde, $form): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $soumissionBc = $form->getData();
            dd($soumissionBc);
            /** ENREGISTREMENT DE FICHIER */
            $nomDeFichier = $this->enregistrementFichier($form);

            /** AJOUT DES INFO NECESSAIRE */
            $soumissionBc->setNumeroCde($numCde)
                ->setUtilisateur($this->getUser()->getUsername())
                ->setPieceJoint1($nomDeFichier)
                ->setStatut(self::STATUT_SOUMISSION)
            ;

            /** ENREGISTREMENT DANS LA BASE DE DONNEE */
            self::$em->persist($soumissionBc);
            self::$em->flush();

            /** COPIER DANS DW */
            //TODO: A REVOIR

            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission('Le document est soumis pour validation', $numCde, 'list_cde_frn', true);
        }
    }

    private function conditionDeBlocage(DaSoumissionBc $soumissionBc, string $numCde): array
    {
        $nomdeFichier = $soumissionBc->getPieceJoint1()->getClientOriginalName();

        return [
            'nomDeFichier' => !preg_match('/^CONTROL COMMANDE.*\b\d{8}\b/', $nomdeFichier),
        ];
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistrementFichier($form): string
    {
        $fieldPattern = '/^pieceJoint(\d{1})$/';
        $nomDeFichie = '';
        foreach ($form->all() as $fieldName => $field) {

            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile|UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {

                    // Cas où c'est un seul fichier
                    $nomDeFichier = $file->getClientOriginalName();
                    $this->traitementDeFichier->upload(
                        $file,
                        $this->cheminDeBase,
                        $nomDeFichier
                    );
                    $nomDeFichie = $nomDeFichier;
                }
            }
        }

        return $nomDeFichie;
    }
}
