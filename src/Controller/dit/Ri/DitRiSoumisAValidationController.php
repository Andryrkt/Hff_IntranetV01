<?php

namespace App\Controller\dit\Ri;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Form\dit\DitRiSoumisAValidationType;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitRiSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Controller\Traits\dit\DitRiSoumisAValidationTrait;
use App\Service\genererPdf\GenererPdfRiSoumisAValidataion;
use App\Service\historiqueOperation\HistoriqueOperationRIService;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitRiSoumisAValidationController extends Controller
{
    use DitRiSoumisAValidationTrait;
    private $historiqueOperation;
    private string $cheminDeBase;
    private TraitementDeFichier $traitementDeFichier;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationRIService($this->getEntityManager());
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/vri/';
        $this->traitementDeFichier = new TraitementDeFichier();
    }

    /**
     * @Route("/soumission-ri/{numDit}", name="dit_insertion_ri")
     *
     * @return void
     */
    public function riSoumisAValidation(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditRiSoumisAValidationModel = new DitRiSoumisAValidationModel();
        $numOrBaseDonner = $ditRiSoumisAValidationModel->recupNumeroOr($numDit);
        if (empty($numOrBaseDonner)) {
            $message = "Le DIT n'a pas encore de numéro OR";

            $this->historiqueOperation->sendNotificationSoumission($message, $numDit, 'dit_index');
        }
        $numOr = $numOrBaseDonner[0]['numor'];
        $ditRiSoumiAValidation = new DitRiSoumisAValidation();
        $ditRiSoumiAValidation
            ->setNumeroDit($numDit)
            ->setNumeroOR($numOrBaseDonner[0]['numor']);

        $itvDejaSoumis = $ditRiSoumisAValidationModel->findItvDejaSoumis($numOr);
        $itvAfficher = $ditRiSoumisAValidationModel->recupInterventionOr($numOr, $itvDejaSoumis);

        $form = $this->getFormFactory()->createBuilder(DitRiSoumisAValidationType::class, $ditRiSoumiAValidation, [
            'itvAfficher' => $itvAfficher
        ])->getForm();

        $this->traitementDuFormulaire($form, $request, $ditRiSoumiAValidation, $numDit, $numOr, $itvAfficher, $ditRiSoumisAValidationModel, $itvDejaSoumis);


        $this->logUserVisit('dit_insertion_ri', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        return $this->render('dit/DitRiSoumisAValidation.html.twig', [
            'form' => $form->createView(),
            'itvAfficher' => $itvAfficher
        ]);
    }

    private function traitementDuFormulaire($form, Request $request, $ditRiSoumiAValidation, $numDit, $numOr, $itvAfficher, $ditRiSoumisAValidationModel, $itvDejaSoumis)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $dataForm = $form->getData();

            // Récupérer les valeurs des cases cochées
            $itvCoches = $this->itvCocher($itvAfficher, $form);

            $conditionDeBlocage = $this->conditionDeBlocageSoumission($ditRiSoumisAValidationModel, $ditRiSoumiAValidation, $numOr, $itvCoches, $itvDejaSoumis, $numDit);

            if ($this->blocage($conditionDeBlocage)) {

                // ajout des informations utiles dans l'entité ditRiSoumiAValidation
                $numeroSoumission = $ditRiSoumisAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());
                $ditRiSoumiAValidation = $this->insertionInfoUtile($dataForm, $ditRiSoumiAValidation, $numeroSoumission, $numDit);

                $genererPdfRi = new GenererPdfRiSoumisAValidataion();


                // ENREGISTRE LE FICHIER
                /** @var UploadedFile $file */
                $file = $form->get("pieceJoint01")->getData();

                $nomDesFichiers = []; // Pour stocker les noms de fichiers générés

                if ($file) {
                    try {
                        // Créer un fichier temporaire
                        $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                        copy($file->getPathname(), $tempFile);

                        foreach ($itvCoches as $value) {
                            try {
                                // Génération du nom de fichier unique
                                $fileName = 'RI_' . $dataForm->getNumeroOR() . '-' . $value . '.' . $file->getClientOriginalExtension();
                                $targetPath = $this->cheminDeBase . $fileName;

                                // Copier vers la destination
                                if (!copy($tempFile, $targetPath)) {
                                    throw new \Exception('Erreur lors de la copie du fichier vers ' . $targetPath);
                                }

                                // Ajouter le nom du fichier au tableau
                                $nomDesFichiers[] = $fileName;
                            } catch (\Exception $e) {
                                $message = 'Le fichier n\'a pas pu être copié pour la valeur : ' . $value;
                                $this->historiqueOperation->sendNotificationSoumission($message, $fileName ?? '-', 'dit_index');
                            }
                        }

                        // Supprimer le fichier temporaire après usage
                        unlink($tempFile);
                    } catch (\Exception $e) {
                        $message = 'Le fichier n\'a pas pu être traité.';
                        $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
                    }
                } else {
                    // Aucun fichier sélectionné
                    $message = 'Aucun fichier n\'a été sélectionné.';
                    $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
                }


                foreach ($itvCoches as $value) {
                    $riSoumisAValidation = new DitRiSoumisAValidation();
                    $riSoumisAValidation
                        ->setNumeroDit($numDit)
                        ->setNumeroOR($dataForm->getNumeroOR())
                        ->setHeureSoumission($this->getTime())
                        ->setDateSoumission(new \DateTime($this->getDatesystem()))
                        ->setNumeroSoumission($numeroSoumission)
                        ->setNumeroItv((int)$value)
                    ;
                    // Persist les entités liées
                    $this->getEntityManager()->persist($riSoumisAValidation);

                    // Génération du PDF
                    $genererPdfRi->copyToDwRiSoumis($value, $riSoumisAValidation->getNumeroOR());
                }

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                // Flushe toutes les entités et l'historique
                $this->getEntityManager()->flush();

                $this->historiqueOperation->sendNotificationSoumission('Le rapport d\'intervention a été soumis avec succès', 'RI_' . $dataForm->getNumeroOR(), 'dit_index', true);
            }
        }
    }

    private function insertionInfoUtile($dataForm, $ditRiSoumiAValidation, $numeroSoumission, $numDit)
    {
        $ditRiSoumiAValidation
            ->setNumeroDit($numDit)
            ->setNumeroOR($dataForm->getNumeroOR())
            ->setHeureSoumission($this->getTime())
            ->setDateSoumission(new \DateTime($this->getDatesystem()))
            ->setNumeroSoumission($numeroSoumission)
        ;
        return $ditRiSoumiAValidation;
    }

    private function itvCocher($itvAfficher, $form)
    {
        $itvCoches = [];

        for ($i = 0; $i < count($itvAfficher); $i++) {
            $checkboxFieldName = 'checkbox_' . $i;
            if ($form->has($checkboxFieldName) && $form->get($checkboxFieldName)->getData()) {
                $itvCoches[] = (int)$itvAfficher[$i]['numeroitv'];
            }
        }
        return $itvCoches;
    }

    private function conditionDeBlocageSoumission($ditRiSoumisAValidationModel, $ditRiSoumiAValidation, $numOr, $itvCoches, $itvDejaSoumis, $numDit): array
    {
        //tous les numéros d'intervention pour cette OR
        $toutNumeroItv = $ditRiSoumisAValidationModel->recupNumeroItv($numOr);

        $existe = false;
        $estSoumis = false;
        foreach ($itvCoches as $value) {
            if (in_array($value, $itvDejaSoumis)) {
                $estSoumis = true;
                break;
            }
            if (!in_array($value, $toutNumeroItv)) {
                $existe = true;
            }
        }

        return [
            'numOrIpsEgalenumOrSql' => $numOr !== $ditRiSoumiAValidation->getNumeroOR(), // le numero OR dans IPS est différent du numero OR dans SQL serveur
            'estSoumis' => $estSoumis, // certaines interventions ont déjà été soumises
            'existe' => $existe // le numero ITV n'existe pas pour le numero OR
        ];
    }

    private function blocage($conditions): bool
    {
        if ($conditions['numOrIpsEgalenumOrSql']) {
            $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";

            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
        } elseif ($conditions['estSoumis']) {
            $message = "Erreur lors de la soumission RI, car certaines interventions ont déjà fait l'objet d'une soumission dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
        } elseif ($conditions['existe']) {
            $message = "Erreur lors de la soumission RI, car certaines interventions n'ont pas encore été validées dans DocuWare.";

            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
        } else {
            return true; // Aucune condition de blocage n'est remplie, la soumission peut continuer
        }
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistrementFichier($form, $numOr): array
    {
        $fieldPattern = '/^pieceJoint(\d{2})$/';
        $nomDesFichiers = [];
        $compteur = 1; // Pour l’indexation automatique

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile !== null) {
                            $extension = $singleFile->guessExtension() ?? $singleFile->getClientOriginalExtension();
                            $nomDeFichier = sprintf('RI_%s-%04d.%s', $numOr, $compteur, $extension);

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
