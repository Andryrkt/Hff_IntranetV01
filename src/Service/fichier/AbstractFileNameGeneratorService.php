<?php

namespace App\Service\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFileNameGeneratorService
{
    /**
     * Génère un nom de fichier selon un format et des variables
     */
    public function generateFileName(
        UploadedFile $file,
        array $options = [],
        int $index = 1
    ): string {
        $defaultOptions = [
            'format' => null,
            'prefixe' => '',
            'generer_nom_callback' => null,
            'variables' => [],
            'index_depart' => 1,
            'sauter_premier_index' => true, // Nouvelle option pour la flexibilité
        ];

        $options = array_merge($defaultOptions, $options);
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();

        if (is_callable($options['generer_nom_callback'])) {
            return call_user_func(
                $options['generer_nom_callback'],
                $file,
                $index,
                $extension,
                $options['variables']
            );
        }

        if ($options['format']) {
            $nomBase = $this->remplacerVariablesFormat(
                $options['format'],
                array_merge([
                    'prefixe' => $options['prefixe'],
                    'index' => $index,
                    'extension' => $extension,
                    'timestamp' => time(),
                    'date' => date('Ymd-His')
                ], $options['variables'])
            );

            return $this->differentierParIndex($nomBase, $index, $options['index_depart'], $extension, $options['sauter_premier_index']);
        }

        return uniqid($options['prefixe'] . '_', true) . '.' . $extension;
    }

    /**
     * Différencie les noms de fichiers par index.
     * Peut sauter le premier index ou commencer l'indexation dès le début.
     * @param bool $sauterPremier Si true, l'indexation commence après le premier élément. Si false, elle commence dès le premier.
     */
    private function differentierParIndex(string $nomBase, int $index, int $indexDepart, string $extension, bool $sauterPremier = true): string
    {
        // Détermine si on doit indexer en fonction de l'option $sauterPremier
        $doitIndexer = $sauterPremier ? ($index > $indexDepart) : ($index >= $indexDepart);

        if ($doitIndexer) {
            // Si on n'a pas sauté le premier, le premier suffixe sera _00 si index et indexDepart sont égaux (ex: 1 et 1).
            $suffixeNumerique = sprintf("_%02d", $index - $indexDepart);
            return preg_replace('/\\.' . preg_quote($extension, '/') . '$/', $suffixeNumerique . '.' . $extension, $nomBase);
        }

        return $nomBase;
    }

    /**
     * Remplace les variables dans le format de nom
     */
    private function remplacerVariablesFormat(string $format, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (strpos($format, '{' . $key . ':') !== false) {
                preg_match('/\\{' . $key . ':([^}]+)\\}/', $format, $matches);
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
     * recupère le nom de fichier
     * 
     * cette methode recupère le nom de fichier dans le première élement d'un tableau envoyer au paramère
     */
    public function getNomFichier(string $nomEtCheminFichiersEnregistrer): string
    {
        $dernierElement = '';
        if (!empty($nomEtCheminFichiersEnregistrer)) {
            $parts = explode('/', $nomEtCheminFichiersEnregistrer);
            $dernierElement = end($parts);
        }

        return $dernierElement;
    }

    public function getCheminEtNomDeFichierSansIndex(string $nomEtCheminFichiersEnregistrer): string
    {
        // Supprimer uniquement le suffixe "_<nombre>" juste avant l’extension
        // Exemple : rapport_15.docx → rapport.docx
        $cheminSansIndex = preg_replace('/(_\d+)(\.[^.]+)$/', '$2', $nomEtCheminFichiersEnregistrer);

        return $cheminSansIndex;
    }
}
