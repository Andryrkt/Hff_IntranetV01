<?php

namespace App\Controller\magasin\bl;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Form\bl\BLSoumissionType;
use App\Factory\bl\BLSoumissionFactory;
use App\Controller\Traits\AutorisationTrait;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdfBlFut;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\historiqueOperation\HistoriqueOperationBLService;

/**
 * @Route("/magasin/sortie-de-pieces-lubs")
 */
class BLSoumissionController extends Controller
{
    use AutorisationTrait;

    private $historiqueOperation;
    private string $cheminDeBase;
    private TraitementDeFichier $traitementDeFichier;
    private GeneratePdfBlFut $generatePdfBlFut;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationBLService($this->getEntityManager());

        // Sécuriser le chemin de base
        if (!isset($_ENV['BASE_PATH_FICHIER'])) {
            throw new \Exception('BASE_PATH_FICHIER non défini');
        }
        $basePath = $_ENV['BASE_PATH_FICHIER'];
        $this->cheminDeBase = rtrim($basePath, '/') . '/bl/';

        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->cheminDeBase)) {
            mkdir($this->cheminDeBase, 0755, true);
        }

        $this->traitementDeFichier = new TraitementDeFichier();
        $this->generatePdfBlFut = new GeneratePdfBlFut();
    }

    /**
     * @Route("/bl-soumission", name="bl_soumission")
     */
    public function createBLSoumission(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->autorisationAcces($this->getUser(), Application::ID_BDL);

        $form = $this->getFormFactory()->createBuilder(BLSoumissionType::class)->getForm();

        $this->traitementFormulaire($form, $request);

        return $this->render('bl/blsoumision.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function traitementFormulaire($form, Request $request)
    {
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return; // Ou gérer les erreurs de validation
        }

        try {

            $typeBl = $form->getData()['typeBl'];

            // Enregistrement des fichiers
            $nomFichiers = $this->enregistrementFichier($form);

            if (empty($nomFichiers)) {
                throw new \Exception('Aucun fichier valide n\'a été téléchargé.');
            }

            // Création de l'entité
            $cheminEtNomFichier = $this->cheminDeBase . $nomFichiers[0];
            $blSoumission = BLSoumissionFactory::createBLSoumission($this->getUser(), $cheminEtNomFichier, $typeBl);

            // Sauvegarde
            $this->getEntityManager()->persist($blSoumission);
            $this->getEntityManager()->flush();

            //envoie dans DW
            if ($typeBl === 1) {
                $this->generatePdfBlFut->copyToDWBlFutInterne($nomFichiers[0]);
            } else {
                $this->generatePdfBlFut->copyToDWBlFutFactureClient($nomFichiers[0]);
            }

            // Historisation et notification
            $message = 'Le document est soumis pour validation';
            $this->historiqueOperation->sendNotificationSoumission($message, 'bl', 'profil_acceuil', true);
        } catch (\Exception $e) {
            // Nettoyer les fichiers uploadés en cas d'erreur
            $this->nettoyerFichiersEnErreur($nomFichiers ?? []);
            throw $e; // Ou gérer l'erreur selon votre architecture
        }
    }

    private function nettoyerFichiersEnErreur(array $nomsFichiers): void
    {
        foreach ($nomsFichiers as $nomFichier) {
            $cheminComplet = $this->cheminDeBase . $nomFichier;
            if (file_exists($cheminComplet)) {
                unlink($cheminComplet);
            }
        }
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
        $compteur = 1;
        $tailleMaximale = 5 * 1024 * 1024; // 5MB
        $extensionsAutorisees = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile !== null) {
                            // Validation du type de fichier
                            if (!$singleFile instanceof UploadedFile) {
                                throw new \InvalidArgumentException('Expected instance of UploadedFile.');
                            }

                            // Validation de la taille
                            if ($singleFile->getSize() > $tailleMaximale) {
                                throw new \InvalidArgumentException('Fichier trop volumineux.');
                            }

                            // Validation de l'extension
                            $extension = $singleFile->guessExtension() ?? $singleFile->getClientOriginalExtension();
                            if (!in_array(strtolower($extension), $extensionsAutorisees)) {
                                throw new \InvalidArgumentException('Extension de fichier non autorisée.');
                            }

                            // Génération du nom unique
                            $nomDeFichier = sprintf(
                                'BL_%s_%s_%s.%s',
                                date('YmdHms'),
                                bin2hex(random_bytes(4)),
                                $compteur,
                                $extension
                            );

                            try {
                                $this->traitementDeFichier->upload(
                                    $singleFile,
                                    $this->cheminDeBase,
                                    $nomDeFichier
                                );
                                $nomDesFichiers[] = $nomDeFichier;
                            } catch (\Exception $e) {
                                // Log l'erreur et continuer ou relancer selon votre besoin
                                error_log("Erreur upload fichier: " . $e->getMessage());
                                throw $e;
                            }

                            $compteur++;
                        }
                    }
                }
            }
        }

        return $nomDesFichiers;
    }
}
