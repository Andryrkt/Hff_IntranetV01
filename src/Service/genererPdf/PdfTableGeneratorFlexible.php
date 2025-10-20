<?php

namespace App\Service\genererPdf;

/**
 * Génère un tableau HTML pour l'export PDF.
 *
 * Ce service peut être configuré pour être plus flexible en appelant la méthode `setOptions()`
 * avant `generateTable()`.
 *
 * Des options peuvent être fournies pour les paramètres globaux du tableau :
 * - `header_row_style`: CSS pour la ligne d'en-tête (ex: 'background-color: #F0F0F0;')
 * - `footer_row_style`: CSS pour la ligne de pied de page.
 * - `empty_message`: Message personnalisé pour les tableaux vides.
 *
 * Le tableau `$headerConfig` pour `generateTable()` a également été amélioré. Le tableau de
 * configuration de chaque colonne peut maintenant inclure :
 * - `type`: ('number', 'date') pour activer le formatage automatique.
 * - `formatter`: Une fonction callable `function($value, $row)` pour un formatage de valeur personnalisé.
 * - `header_style`: CSS spécifique pour la cellule d'en-tête.
 * - `cell_style`: CSS spécifique pour les cellules du corps.
 * - `styler`: Une fonction callable `function($value, $row)` pour appliquer des styles dynamiques à une cellule (ex: basé sur la valeur).
 * - `footer_style`: CSS spécifique pour la cellule de pied de page.
 */
class PdfTableGeneratorFlexible
{
    private array $options = [];

    /**
     * Définit les options pour la génération du tableau.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function generateTable(array $headerConfig, array $rows, array $totals, bool $expre = false)
    {
        $tableAttributes = $this->options['table_attributes'] ?? 'border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;"';
        $html = '<table ' . $tableAttributes . '>';
        $html .= $this->generateHeader($headerConfig);
        $html .= $this->generateBody($headerConfig, $rows, $expre);
        $html .= $this->generateFooter($headerConfig, $totals);
        $html .= '</table>';

        // Réinitialise les options après la génération pour ne pas affecter les tableaux suivants
        $this->options = [];

        return $html;
    }

    private function generateHeader(array $headerConfig)
    {
        $headerRowStyle = $this->options['header_row_style'] ?? 'background-color: #D3D3D3;';
        $html = '<thead><tr style="' . $headerRowStyle . '">';
        foreach ($headerConfig as $config) {
            $style = $config['header_style'] ?? $config['style'];
            $html .= '<th style="width: ' . $config['width'] . 'px; ' . $style . '">' . $config['label'] . '</th>';
        }
        $html .= '</tr></thead>';
        return $html;
    }

    /**
     * Génère le corps du tableau.
     *
     * @param array $headerConfig
     * @param array $rows
     * @param boolean $expre Si vrai, n'affiche pas de message pour un tableau vide.
     * @return string
     */
    private function generateBody(array $headerConfig, array $rows, bool $expre = false)
    {
        $html = '<tbody>';
        $emptyMessage = $this->options['empty_message'] ?? 'N/A';

        if (empty($rows) && !$expre) {
            $html .= '<tr><td colspan="' . count($headerConfig) . '" style="text-align: center; font-weight: bold;">' . $emptyMessage . '</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        foreach ($rows as $row) {
            // L'implémentation précédente avait une vérification boguée et inefficace pour les lignes avec des montants à zéro.
            // Elle a été supprimée pour nettoyer le code. Une fonctionnalité similaire peut être implémentée
            // en filtrant les données `$rows` avant d'appeler cette méthode, ou réintroduite via une option.

            $html .= '<tr>';
            foreach ($headerConfig as $config) {
                $key = $config['key'];
                $value = '';

                if (is_array($row) || $row instanceof \ArrayAccess) {
                    $value = $row[$key] ?? '';
                } elseif (is_object($row)) {
                    // Tenter de lire la propriété publique
                    if (isset($row->{$key})) {
                        $value = $row->{$key};
                    } else {
                        // Sinon, tenter d'appeler un getter
                        $getter = 'get' . ucfirst($key);
                        if (method_exists($row, $getter)) {
                            $value = $row->{$getter}();
                        }
                    }
                }

                $baseStyle = $config['cell_style'] ?? str_replace('font-weight: bold;', '', $config['style']);

                $dynamicStyle = '';
                if (isset($config['styler']) && is_callable($config['styler'])) {
                    $dynamicStyle = $config['styler']($value, $row);
                } else {
                    $dynamicStyle = $this->getDynamicStyle($key, $value);
                }
                $style = $baseStyle . $dynamicStyle;

                if (isset($config['formatter']) && is_callable($config['formatter'])) {
                    $value = $config['formatter']($value, $row);
                } else {
                    $value = $this->formatValue($key, $value, $config);
                }

                $html .= '<td style="width: ' . $config['width'] . 'px; ' . $style . '">' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        return $html;
    }

    private function generateFooter(array $headerConfig, array $totals)
    {
        $footerRowStyle = $this->options['footer_row_style'] ?? 'background-color: #D3D3D3;';
        $html = '<tfoot><tr style="' . $footerRowStyle . '">';
        foreach ($headerConfig as $config) {
            $key = $config['key'];
            $style = $config['footer_style'] ?? $config['style'];
            $value = $totals[$key] ?? '';

            if (!empty($value)) {
                if (isset($config['formatter']) && is_callable($config['formatter'])) {
                    $value = $config['formatter']($value, $totals);
                } else {
                    $value = $this->formatValue($key, $value, $config);
                }
            }

            $html .= '<th style="width: ' . $config['width'] . 'px; ' . $style . '">' . $value . '</th>';
        }
        $html .= '</tr></tfoot>';
        return $html;
    }


    private function getDynamicStyle($key, $value)
    {
        $styles = '';
        if ($key === 'statut') {
            switch ($value) {
                case 'Supp':
                    $styles .= 'background-color: #FF0000;';
                    break;
                case 'Modif':
                    $styles .= 'background-color: #FFFF00;';
                    break;
                case 'Nouv':
                    $styles .= 'background-color: #00FF00;';
                    break;
            }
        }
        return $styles;
    }

    /**
     * Formate une valeur en fonction de son type, défini dans la configuration de la colonne ou déduit de la clé.
     * Le format de nombre peut être spécifié via `type`='number' dans la configuration de la colonne.
     * Le format de date peut être spécifié via `type`='date' dans la configuration de la colonne.
     *
     * @param string $key La clé de la ligne de données.
     * @param mixed $value La valeur à formater.
     * @param array $config La configuration pour la colonne.
     * @return string La valeur formatée.
     */
    private function formatValue(string $key, $value, array $config = []): string
    {
        $type = $config['type'] ?? null;

        // Formatage des nombres
        $isNumeric = $type === 'number';
        if ($type === null && (in_array($key, ['mttTotal', 'mttPieces', 'mttMo', 'mttSt', 'mttLub', 'mttAutres', 'mttTotalAv', 'mttTotalAp', 'pu1', 'pu2', 'pu3', 'prixHt', 'montantNet', 'remise1', 'remise2']) || stripos($key, 'mtt') !== false)) {
            $isNumeric = true;
        }

        if ($isNumeric) {
            if (is_numeric($value)) {
                return number_format((float) $value, 2, ',', '.');
            }
            return '0,00';
        }

        // Formatage des dates
        $isDate = $type === 'date';
        if ($type === null && stripos($key, 'date') !== false) {
            $isDate = true;
        }

        if ($isDate) {
            // Vérifier si la valeur est une chaîne et non égale à '-'
            if (is_string($value) && !empty($value) && $value !== '-') {
                try {
                    $date = new \DateTime($value);
                    return $date->format('d/m/Y');
                } catch (\Exception $e) {
                    // Si la date est invalide, retourne une valeur par défaut
                    return '-';
                }
            }
            return '-'; // Si la valeur n'est pas valide, retourne un séparateur par défaut
        }

        // Retourne la valeur non modifiée si aucune condition ne s'applique
        return (string) $value;
    }
}
