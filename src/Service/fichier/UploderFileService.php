<?php

namespace App\Service\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormInterface;

class UploderFileService
{
    private string $cheminDeBase;
    private AbstractFileNameGeneratorService $nameGenerator;

    public function __construct(string $cheminDeBase, AbstractFileNameGeneratorService $nameGenerator)
    {
        $this->cheminDeBase = $cheminDeBase;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Enregistre les fichiers avec des options flexibles
     */
    public function enregistrementFichier(
        FormInterface $form,
        array $options = []
    ): array {
        $defaultOptions = [
            'pattern' => '/^pieceJoint(\d+)$/',
            'repertoire' => null,
            'prefixe' => '',
            'format_nom' => null,
            'index_depart' => 1,
            'generer_nom_callback' => null,
            'variables' => [],
        ];

        $options = array_merge($defaultOptions, $options);
        $nomDesFichiers = [];
        $compteur = $options['index_depart'];

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($options['pattern'], $fieldName, $matches)) {
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile instanceof UploadedFile) {
                            $nomFichier = $this->nameGenerator->generateFileName(
                                $singleFile,
                                [
                                    'format' => $options['format_nom'],
                                    'prefixe' => $options['prefixe'],
                                    'generer_nom_callback' => $options['generer_nom_callback'],
                                    'variables' => $options['variables'],
                                    'index_depart' => $options['index_depart'],
                                ],
                                $compteur
                            );

                            $repertoireFinal = $this->getRepertoireFinal($options);

                            $this->upload($singleFile, $repertoireFinal, $nomFichier);

                            $nomDesFichiers[] = [
                                'nom_fichier' => $nomFichier,
                                'chemin_complet' => $repertoireFinal . $nomFichier,
                                'index' => $compteur
                            ];

                            $compteur++;
                        }
                    }
                }
            }
        }

        return $nomDesFichiers;
    }

    /**
     * Upload un fichier
     */
    public function upload(UploadedFile $file, string $cheminDeBase, string $fileName): void
    {
        if (!file_exists($file->getPathname())) {
            throw new \RuntimeException("Le fichier temporaire n'existe plus : " . $file->getPathname());
        }

        try {
            $file->move($cheminDeBase, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors du téléchargement du fichier : " . $e->getMessage());
        }
    }

    /**
     * Détermine le répertoire final
     */
    private function getRepertoireFinal(array $options): string
    {
        return $options['repertoire'] ?: $this->cheminDeBase;
    }

    /**
     * Enregistre les fichiers et retourne uniquement les noms des fichiers
     */
    public function getNomsFichiers(FormInterface $form, array $options = []): array
    {
        $resultatsComplets = $this->enregistrementFichier($form, $options);
        return empty($resultatsComplets) ? [] : array_column($resultatsComplets, 'nom_fichier');
    }

    /**
     * Enregistre les fichiers et retourne les chemins complets
     */
    public function getNomsEtCheminFichiers(FormInterface $form, array $options = []): array
    {
        $resultatsComplets = $this->enregistrementFichier($form, $options);
        return empty($resultatsComplets) ? [] : array_column($resultatsComplets, 'chemin_complet');
    }

    /**
     * Enregistre les fichiers et retourne :
     * [
     *   [ tous les chemins complets ],
     *   [ tous les noms de fichiers ]
     * ]
     *
     * @param FormInterface $form
     * @param array $options
     * @return array
     */
    public function getFichiers(FormInterface $form, array $options = []): array
    {
        $resultatsComplets = $this->enregistrementFichier($form, $options);

        if (empty($resultatsComplets)) {
            return [[], []];
        }

        $chemins = array_column($resultatsComplets, 'chemin_complet');
        $noms = array_column($resultatsComplets, 'nom_fichier');

        return [$chemins, $noms];
    }
}
