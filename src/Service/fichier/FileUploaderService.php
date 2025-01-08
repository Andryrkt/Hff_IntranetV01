<?php

namespace App\Service\fichier;

use App\Service\FusionPdf;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Exception\RuntimeException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class FileUploaderService
{
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
    const ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];
    private string $targetDirectory;
    private FusionPdf $fusionPdf;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
        $this->fusionPdf = new FusionPdf();
    }

    public function upload(UploadedFile $file, string $prefix = ''): string
    {
        $fileName = $prefix . $this->generateUniqueFileName() . '.' . $file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Une erreur est survenue lors du téléchargement du fichier.");
        }

        return $fileName;
    }

    private function generateUniqueFileName(): string
    {
        return uniqid();
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }


    /**
     * Génère un nom de fichier 
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @param int $index
     * @return string
     */
    private function generateNomDeFichier(UploadedFile $file,  string $numeroDoc, int $index, string $prefix = '', string $numeroVersion = ''): string
    {
        return sprintf(
            '%s_%s%s_%02d.%s',
            $prefix,
            $numeroDoc,
            $numeroVersion,
            $index,
            $file->guessExtension()
        );
    }


    private function genererateCheminMainFichier(string $numeroDoc, string $numeroVersion = ''): string
    {
        return sprintf(
            '%s_%s%s.pdf',
            $this->targetDirectory,
            $numeroDoc,
            $numeroVersion
        );
    }

    /**
     * Upload un fichier après validation.
     *
     * @param UploadedFile $file
     * @param string $numeroDoc
     * @param int|null $index
     * @param string $prefix
     * @param string $numeroVersion
     * @return string|null
     */
    private function uploadFile(UploadedFile $file,  string $numeroDoc, int $index, string $prefix = '', string $numeroVersion = ''): ?string
    {
        if (
            !$file->isValid() ||
            !in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_EXTENSIONS, true) ||
            !in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)
        ) {
            throw new InvalidArgumentException("Type de fichier non autorisé : {$file->getClientOriginalName()}.");
        }

        $fileName = $this->generateNomDeFichier( $file,  $numeroDoc, $index, $prefix, $numeroVersion);

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        return $this->targetDirectory . $fileName;
    }

    /**
 * Méthode pour récupérer les fichiers téléchargés correspondant à un motif spécifique
 *
 * @param FormInterface $form
 * @param string $fieldPattern
 * @param string $numeroDoc
 * @param string $prefix
 * @param string $numeroVersion
 * @return array Liste des chemins des fichiers téléchargés
 */
private function getUploadedFiles(
    FormInterface $form,
    string $fieldPattern,
    string $numeroDoc,
    string $prefix,
    string $numeroVersion
): array {
    $uploadedFiles = [];

    foreach ($form->all() as $fieldName => $field) {
        if (preg_match($fieldPattern, $fieldName)) {
            /** @var UploadedFile|null $file */
            $file = $field->getData();
            if ($file !== null) {
                // Extraire l'index ou identifiant si nécessaire
                if (preg_match($fieldPattern, $fieldName, $matches)) {
                    $index = isset($matches[1]) ? (int)$matches[1] : null;
                    $uploadedFilePath = $this->uploadFile($file, $numeroDoc, $index, $prefix, $numeroVersion);

                    if ($uploadedFilePath !== null) {
                        $uploadedFiles[] = $uploadedFilePath;
                    }
                }
            }
        }
    }

    return $uploadedFiles;
}

    /**
     * Méthode qui permet d'enregistrer les fichiers telecharger ou le fusionner puis l'enregistrer après
     *
     * @param FormInterface $form
     * @param string $prefix sotn les mots qui commence le nom du fichier
     * @param string $numeroDoc pour le numero document exemple : numeroOR, numeroDevis, numeroDIt, ...
     * @param bool $mergeFiles permet de savoir s'il faut fusioner ou nom le fichier
     *           - true : valeur par defaut, on fusionne le fichier
     *           - false : si on ne fusionne pas le fichier
     * @param string $numeroVersion  
     *          - format : "_<numero>" //n'oublier pas le endercore
     *          - ce n'est pas obligatoire de le mettre
     *          - par defaut vide 
     * @param string $fieldPattern
     * @return string retourne le nom de fichier fusionner ou non
     */
    public function chargerEtOuFusionneFichier(
        FormInterface $form,
        string  $prefix,
        string $numeroDoc, 
        bool $mergeFiles = true,
        string $numeroVersion = '',
        string $fieldPattern = '/^pieceJoint\d{2}$/'
    ): string 
    {
        $uploadedFiles = [];
        $mainFilePath = $this->genererateCheminMainFichier($numeroDoc, $numeroVersion);
        // Ajouter le fichier principal en tête du tableau, s'il existe
        if (!file_exists($mainFilePath)) {
            throw new \RuntimeException('Le fichier principal n\'existe pas.');
        }
        $uploadedFiles[] = $mainFilePath;
    
         // Récupérer les fichiers téléchargés
        $uploadedFiles = array_merge($uploadedFiles, $this->getUploadedFiles($form, $fieldPattern, $numeroDoc, $prefix, $numeroVersion));
    
        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainFilePath;

        // Fusionner les fichiers si demandé
        if ($mergeFiles && !empty($uploadedFiles)) {
            $this->fusionPdf->mergePdfs($uploadedFiles, $mergedPdfFile);
        }

        return $mergedPdfFile;
    }
}
