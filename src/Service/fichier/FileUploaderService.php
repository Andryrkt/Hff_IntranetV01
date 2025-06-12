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
     * @param string $index
     * @return string
     */
    private function generateNomDeFichier(?string $extension,  string $numeroDoc, string $index, string $prefix = '', string $numeroVersion = ''): string
    {
        $extension = $extension ?? 'pdf';

        return sprintf(
            '%s_%s%s%s.%s',
            $prefix,
            $numeroDoc,
            $numeroVersion !== '' ? "_{$numeroVersion}" : '',
            $index !== '' ? "_0{$index}" : '',
            $extension
        );
    }


    private function genererateCheminMainFichier(string $numeroDoc, string $prefix, string $numeroVersion = ''): string
    { 
        return sprintf(
            '%s_%s%s.pdf',
            $prefix,
            $numeroDoc,
            $numeroVersion !== '' ? "_{$numeroVersion}" : ''
        );
    }

    /**
     * Upload un fichier après validation.
     *
     * @param UploadedFile $file
     * @param string $numeroDoc
     * @param string $index
     * @param string $prefix
     * @param string $numeroVersion
     * @return string|null
     */
    public function uploadFile(
        UploadedFile $file,  
        string $numeroDoc, 
        string $index, 
        string $prefixFichier = '', 
        string $numeroVersion = '',
        string $pathFichier = 'fichiers/'
        ): ?string
    {
        if (
            !$file->isValid() ||
            !in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_EXTENSIONS, true) ||
            !in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)
        ) {
            throw new InvalidArgumentException("Type de fichier non autorisé : {$file->getClientOriginalName()}.");
        }

        $extension =$file->guessExtension();
        $prepareNom = [
            'prefix' => $prefixFichier,
            'numeroDoc' => $numeroDoc,
            'numeroVersion' => $numeroVersion,
            'index' => $index,
            'extension' => $extension
        ];
        $fileName = GenererNonFichierService::genererNonFichier($prepareNom );
        $destination = $this->targetDirectory . $pathFichier;

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        return $destination . $fileName;
    }


/**
 * Méthode pour récupérer les fichiers téléchargés correspondant à un motif spécifique.
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
    string $fieldPattern = '/^pieceJoint(\d{2})$/',
    string $numeroDoc,
    string $prefixFichier,
    string $numeroVersion = '',
    string $pathFichier = 'fichiers/',
    bool $isIndex = true
): array {
    $uploadedFiles = [];

    foreach ($form->all() as $fieldName => $field) {
        if (preg_match($fieldPattern, $fieldName, $matches)) {
            /** @var UploadedFile|null $file */
            $file = $field->getData();
            if ($file !== null) {
                // Récupérer l'index ou identifiant depuis les correspondances
                if($isIndex){
                    $index = isset($matches[1]) ? (string)$matches[1] : '';
                } else {
                    $index = '';
                }

                // Appeler la méthode uploadFile
                $uploadedFilePath = $this->uploadFile($file, $numeroDoc, $index, $prefixFichier, $numeroVersion, $pathFichier);

                if ($uploadedFilePath !== null) {
                    $uploadedFiles[] = $uploadedFilePath;
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
     * @param array $options
     *  + string  $prefix, sont les mots qui commence le nom du fichier
     *   + string $numeroDoc, pour le numero document (exemple : numeroOR, numeroDevis, numeroDIt, ...)
     *   + bool $mergeFiles = true, permet de savoir s'il faut fusioner ou nom le fichier
     *            - true : valeur par defaut, on fusionne le fichier
     *           - false : si on ne fusionne pas le fichier
     *   + string $numeroVersion = '',
     *           - ce n'est pas obligatoire de le mettre
     *          - par defaut vide
     *   + bool $mainFirstPage = false, est-ce que le fichier principal doit être en première page (par défaut false qui dise que le fichier principal est en premier page)
     *   + string $pathFichier = 'fichiers/', chemin ou on mis le fichier telecharger (par défaut 'fichiers/')
     *   + string $fieldPattern = '/^pieceJoint(\d{2})$/' Motif pour récupérer les fichiers (par défaut '/^pieceJoint(\d{2})$/')
     *  + bool $isIndex = true, est-ce que le fichier a un index (par défaut true)
     *  @return string retourne le nom de fichier fusionner ou non
     */
    public function chargerEtOuFusionneFichier(
        FormInterface $form,
        array $options = []
    ): string 
    {
        $prefix = $options['prefix'] ?? '';
        $prefixFichier = $options['prefixFichier'] ?? $prefix;
        $numeroDoc = $options['numeroDoc'] ?? '';
        $mergeFiles = $options['mergeFiles'] ?? true;
        $numeroVersion = $options['numeroVersion'] ?? '';
        $mainFirstPage = $options['mainFirstPage'] ?? false;
        $fieldPattern = $options['fieldPattern'] ?? '/^pieceJoint(\d{2})$/';
        $pathFichier = $options['pathFichier'] ?? 'fichiers/';
        $isIndex = $options['isIndex'] ?? true;

        $uploadedFiles = [];
        $prepareNom = [
            'prefix' => $prefix,
            'numeroDoc' => $numeroDoc,
            'numeroVersion' => $numeroVersion
        ];
        $mainFileName = GenererNonFichierService::genererNonFichier( $prepareNom);
        $mainFilePathName = $this->targetDirectory. $mainFileName;
        // dump($mainFilePathName);
        // dd(file_exists($mainFilePathName));
        
        if($mainFirstPage){
            $uploadedFiles[] = $mainFilePathName;
            $uploadedFiles = array_merge( 
                $this->getUploadedFiles($form, $fieldPattern, $numeroDoc, $prefixFichier, $numeroVersion, $pathFichier, $isIndex), 
                $uploadedFiles
            );
        } else {
            $uploadedFiles[] = $mainFilePathName;
            $uploadedFiles = array_merge(
                $uploadedFiles, 
                $this->getUploadedFiles($form, $fieldPattern, $numeroDoc, $prefixFichier, $numeroVersion, $pathFichier, $isIndex),
            );
        }
        
        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainFilePathName;

        // Fusionner les fichiers si demandé
        if ($mergeFiles && !empty($uploadedFiles)) {
            $this->fusionPdf->mergePdfs($uploadedFiles, $mergedPdfFile);
        }

        return $mainFileName;
    }



    /**
     * Permet de récupérer les chemins des fichiers téléchargés correspondant à un motif spécifique dans un tableau
     *
     * @param FormInterface $form
     * @param array $options
     * @return array
     */
    public function getPathFiles(
        FormInterface $form,
        array $options
    ): array {

        $prefixFichier = $options['prefixFichier'] ?? '';
        $numeroDoc = $options['numeroDoc'] ?? '';
        $numeroVersion = $options['numeroVersion'] ?? '';
        $fieldPattern = $options['fieldPattern'] ?? '/^pieceJoint(\d{2})$/';
        $pathFichier = $options['pathFichier'] ?? 'fichiers/';
        $isIndex = $options['isIndex'] ?? true;

        $uploadedFiles = [];
    
        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile|null $file */
                $file = $field->getData();
                if ($file !== null) {
                    // Récupérer l'index ou identifiant depuis les correspondances
                    if($isIndex){
                        $index = isset($matches[1]) ? (string)$matches[1] : '';
                    } else {
                        $index = '';
                    }
    
                    // Appeler la méthode uploadFile
                    $uploadedFilePath = $this->uploadFile($file, $numeroDoc, $index, $prefixFichier, $numeroVersion, $pathFichier);
    
                    if ($uploadedFilePath !== null) {
                        $uploadedFiles[] = $uploadedFilePath;
                    }
                }
            }
        }
    
        return $uploadedFiles;
    }

    /**
     * Methode qui permet de crée un tableau qui contient les chemis de fichier à fusionner
     * il permet aussi de specifier le position du page principal
     * position 0 si le fichier principal doit etre en premier page
     * position 1 si le fichier principal doit etre en deuxieme page
     *  ex : ['fichier1.pdf', 'fichier2.pdf', 'fichier3.pdf', 'fichier4.pdf']
     *
     * @param array $uploadedFiles
     * @param string $mainFilePathName
     * @param integer $position
     * @return array
     */
    public function insertFileAtPosition(array $uploadedFiles, string $mainFilePathName, int $position = 0): array {
        // S'assurer que la position est valide
        $position = max(0, min($position, count($uploadedFiles))); 
    
        // Insérer le fichier principal à la position spécifiée
        array_splice($uploadedFiles, $position, 0, [$mainFilePathName]);
    
        return $uploadedFiles;
    }

    public function fusionFichers(array $uploadedFiles, $nomFichierFusioner)
    {
        $this->fusionPdf->mergePdfs($uploadedFiles, $nomFichierFusioner);
    }
    
     /**
     * Upload un fichier après validation.
     *
     * @param UploadedFile $file
     * @param string $numeroDoc
     * @param string $index
     * @param string $prefix
     * @param string $numeroVersion
     * @return string|null
     */
    public function uploadFileSansName(
        UploadedFile $file,  
        string $fileName = '',
        string $pathFichier = 'fichiers/'
        ): ?string
    {
        if (
            !$file->isValid() ||
            !in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_EXTENSIONS, true) ||
            !in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)
        ) {
            throw new InvalidArgumentException("Type de fichier non autorisé : {$file->getClientOriginalName()}.");
        }
        
        $destination = $this->targetDirectory . $pathFichier;

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        return $destination . $fileName;
    }
}