<?php

namespace App\Service\fichier;

use App\Service\GlobalVariablesService;

class GenererNonFichierService
{
    /**
     * Methode qui permet de generer un nom de fichier 
     *
     * @param string $prefix
     * @param string $numeroDoc
     * @param string $numeroVersion
     * @param string $index
     * @param string $extension
     * @return string
     */
    public static function  genererNonFichier(array $options): string
    {
        $prefix = $options['prefix'] ?? '';
        $numeroDoc = $options['numeroDoc'] ?? '';
        $numeroVersion = $options['numeroVersion'] ?? '';
        $index = $options['index'] ?? '';
        $extension = $options['extension']?? 'pdf';

        return sprintf(
            '%s%s%s%s%s',
            $prefix !== '' ? "{$prefix}" : '',
            $numeroDoc !== '' ? "_{$numeroDoc}" : '',
            $numeroVersion !== '' ? "_{$numeroVersion}" : '',
            $index !== '' ? "_0{$index}" : '',
            '.'.$extension
        );
    }

    /**
     * Methode qui generer le chemin de fichier
     *
     * @param string $path
     * @param string $nomFichier
     * @return string
     */
    public static function genererPathFichier(string $path, string $nomFichier): string
    {
        return $path . $nomFichier;
    }

    /**
     * Génère un nom de fichier dynamique en fonction des options fournies.
     *
     * @param array $options Tableau associatif contenant les éléments du nom de fichier.
     *     Ex : ['prefix' => 'DOC', 'numeroDoc' => '123', 'numeroVersion' => '2', 'index' => '01', 'suffixe' => 'oui', 'extension' => 'pdf']
     * @param string $separator Séparateur entre les parties principales du nom (par défaut '_').
     * @param string $suffixSeparator Séparateur entre la partie principale et le suffixe (par défaut '-').
     * @param string $defaultExtension Extension par défaut (par défaut '.pdf').
     * @return string Nom de fichier généré.
     */
    public static function generationNomFichier(
        array $options,
        string $separator = '_',
        string $suffixSeparator = '-',
        string $defaultExtension = '.pdf'
    ): string {
        // Définition de l'ordre des clés principales pour assurer une cohérence
        $order = ['prefix', 'numeroDoc', 'numeroVersion'];

        // Vérification et récupération des valeurs
        $extension = $options['extension'] ?? $defaultExtension;
        $suffixe = $options['suffixe'] ?? ''; // Peut être "C", "P" ou vide

        // Construction de la partie principale en respectant l'ordre défini
        $parts = [];
        foreach ($order as $key) {
            if (!empty($options[$key])) {
                $parts[] = $options[$key];
            }
        }

        // Ajout de l'index avec le préfixe #
        if (!empty($options['index'])) {
            $parts[] = $options['index'] . '#';
        }

        // Construction du nom de fichier
        $filename = implode($separator, $parts);

        // Ajout du suffixe si présent (ex: "-C", "-P")
        if (!empty($suffixe)) {
            $filename .= $suffixSeparator . $suffixe;
        }

        // Ajout de l'extension
        return $filename . $extension;
    }

    public static function pieceGererMagasinConstructeur($constructeur)
    {
        if (isset($constructeur[0])) {
            $containsCAT = count(array_unique($constructeur[0])) === "CAT";
            $containsOther = count(array_filter($constructeur[0], fn($el) => $el !== "CAT"));
            $containsNonCAT = !empty(array_intersect(GlobalVariablesService::get('pieceMagasinSansCat'), $constructeur[0]));

            if ($containsCAT) {
                $suffix = 'C';
            } else if ($containsNonCAT) {
                $suffix = 'P';
            } else if ($containsOther > 0) {
                $suffix = 'CP';
            } else {
                $suffix = 'N';
            }
        } else {
            $suffix = 'N';
        }

        return $suffix;
    }
}
