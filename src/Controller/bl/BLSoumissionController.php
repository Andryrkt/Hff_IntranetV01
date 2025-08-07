<?php

namespace App\Controller\bl;

use App\Controller\Controller;
use App\Form\bl\BLSoumissionType;
use App\Factory\bl\BLSoumissionFactory;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationBLService;

/**
 * @Route("/bl")
 */
class BLSoumissionController extends Controller
{
    private $historiqueOperation;
    private string $cheminDeBase;
    private TraitementDeFichier $traitementDeFichier;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationBLService();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/bl/';
        $this->traitementDeFichier = new TraitementDeFichier();
    }
    /**
     * @Route("/bl-soumission", name="bl_soumission")
     */
    public function createBLSoumission(Request $request)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(BLSoumissionType::class)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array */
            $nomFichiers = $this->enregistrementFichier($form, 'BL');

            /** @var string */
            $cheminEtNomFichier = $this->cheminDeBase . $nomFichiers[0];
            dd($cheminEtNomFichier);
            // Convert DTO to Entity
            // $blSoumission = BLSoumissionFactory::createBLSoumission(Controller::getUser(), $cheminEtNomFichier);

            // Save the entity
            // self::$em->persist($blSoumission);
            // self::$em->flush();

            // Redirect or display success message
            /** HISTORISATION */
            $message = 'Le document est soumis pour validation';
            $this->historiqueOperation->sendNotificationSoumission($message, 'bl', 'profil_acceuil', true);
        }


        self::$twig->display('bl/blsoumision.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistrementFichier($form): array
    {
        $fieldPattern = '/^pieceJoint(\d{1})$/';
        $nomDesFichiers = [];
        $compteur = 1; // Pour l’indexation automatique

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile|UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile !== null) {
                            // Ensure $singleFile is an instance of Symfony's UploadedFile
                            if (!$singleFile instanceof UploadedFile) {
                                throw new \InvalidArgumentException('Expected instance of Symfony\Component\HttpFoundation\File\UploadedFile.');
                            }

                            $extension = $singleFile->guessExtension() ?? $singleFile->getClientOriginalExtension();
                            $nomDeFichier = sprintf('BL_%s-%04d.%s', date('Ymd'), $compteur, $extension);

                            $this->traitementDeFichier->upload(
                                $singleFile,
                                $this->cheminDeBase,
                                $nomDeFichier
                            );

                            $nomDesFichiers[] = $nomDeFichier;
                            $compteur++;
                        }
                    }
                }
            }
        }

        return $nomDesFichiers;
    }
}
