<?php

namespace App\Service\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormInterface;

class UploderFileService
{
    private string $cheminDeBase;

    public function __construct(string $cheminDeBase)
    {
        $this->cheminDeBase = $cheminDeBase;
    }

    /**
     * Enregistre les fichiers avec des options flexibles de nommage
     */
    public function enregistrementFichier(
        FormInterface $form,
        array $options = []
    ): array {
        $defaultOptions = [
            'pattern' => '/^pieceJoint(\d+)$/',
            'repertoire' => null,
            'prefixe' => '',
            'format_nom' => null, // Priorité à la fonction de génération de nom
            'index_depart' => 1,
            'generer_nom_callback' => null, // Callback pour générer le nom personnalisé
            'variables' => [], // Variables supplémentaires pour le nommage
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
                            $nomFichier = $this->genererNomFichier(
                                $singleFile,
                                $options,
                                $compteur
                            );

                            $repertoireFinal = $this->getRepertoireFinal($options);

                            $this->upload(
                                $singleFile,
                                $repertoireFinal,
                                $nomFichier
                            );

                            $nomDesFichiers[] = [
                                'nom_fichier' => $nomFichier,
                                'chemin_complet' => $repertoireFinal . '/' . $nomFichier,
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

    public function upload(UploadedFile $file, string $cheminDeBase, string $fileName): void
    {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException("Le fichier fourni n'est pas une instance de UploadedFile.");
        }

        if (!file_exists($file->getPathname())) {
            throw new \RuntimeException("Le fichier temporaire n'existe plus : " . $file->getPathname());
        }

        try {
            // Debug : chemin réel du fichier temporaire
            // dd($file->getRealPath());

            $file->move($cheminDeBase, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors du téléchargement du fichier : " . $e->getMessage());
        }
    }

    /**
     * Génère le nom du fichier selon les options
     */
    private function genererNomFichier(
        UploadedFile $file,
        array $options,
        int $index
    ): string {
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();

        // Priorité au callback personnalisé
        if (is_callable($options['generer_nom_callback'])) {
            return call_user_func(
                $options['generer_nom_callback'],
                $file,
                $index,
                $extension,
                $options['variables']
            );
        }

        // Fallback au format défini
        if ($options['format_nom']) {
            return $this->remplacerVariablesFormat(
                $options['format_nom'],
                array_merge([
                    'prefixe' => $options['prefixe'],
                    'index' => $index,
                    'extension' => $extension,
                    'timestamp' => time(),
                    'date' => date('Ymd-His')
                ], $options['variables'])
            );
        }

        // Fallback par défaut
        return uniqid($options['prefixe'] . '_', true) . '.' . $extension;
    }

    /**
     * Remplace les variables dans le format de nom
     */
    private function remplacerVariablesFormat(string $format, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (strpos($format, '{' . $key . ':') !== false) {
                // Gère les formats comme {index:04d}
                preg_match('/\{' . $key . ':([^}]+)\}/', $format, $matches);
                if (isset($matches[1])) {
                    $formattedValue = sprintf('%' . $matches[1], $value);
                    $format = str_replace($matches[0], $formattedValue, $format);
                }
            } else {
                $format = str_replace('{' . $key . '}', (string)$value, $format);
            }
        }

        return $format;
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

        if (empty($resultatsComplets)) {
            return [];
        }

        return array_column($resultatsComplets, 'nom_fichier');
    }

    /**
     * Enregistre les fichiers et retourne uniquement les chemins et noms des fichiers (nom complet)
     */
    public function getNomsEtCheminFichiers(FormInterface $form, array $options = []): array
    {
        $resultatsComplets = $this->enregistrementFichier($form, $options);

        if (empty($resultatsComplets)) {
            return [];
        }

        return array_column($resultatsComplets, 'chemin_complet');
    }
}
