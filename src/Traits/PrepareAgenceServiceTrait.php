<?php

namespace App\Traits;

trait PrepareAgenceServiceTrait
{
    /**
     * Prépare les données de choix pour les selects agence et service.
     *
     * @param array $agenceServiceAutorises Tableau associatif indexé par un identifiant unique de ligne,
     *                                      chaque entrée contenant les clés :
     *                                      - agence_id     : identifiant de l'agence
     *                                      - agence_code   : code de l'agence
     *                                      - agence_libelle: libellé de l'agence
     *                                      - service_code  : code du service
     *                                      - service_libelle: libellé du service
     *
     * @return array{
     *     agenceChoices: array<string, int>,
     *     serviceChoices: array<string, int|string>,
     *     serviceAttr: array<int|string, array<string, int>>
     * }
     *
     * Retourne un tableau associatif contenant :
     *   - agenceChoices  : [label agence => agence_id]         (sans doublons de label)
     *   - serviceChoices : [label service unique => id de ligne] (doublons de label autorisés, clé rendue unique)
     *   - serviceAttr    : [id de ligne => ['data-agence' => agence_id]] (attributs HTML des options service)
     */
    private function prepareAgenceServiceChoicesById(array $agenceServiceAutorises): array
    {
        $agenceChoices  = [];
        $serviceChoices = [];
        $serviceAttr    = [];

        foreach ($agenceServiceAutorises as $id => $item) {
            // --- Agence : on évite les doublons sur le label ---
            $agenceLabel = $item['agence_code'] . ' ' . $item['agence_libelle'];
            if (!isset($agenceChoices[$agenceLabel])) {
                $agenceChoices[$agenceLabel] = $item['agence_id'];
            }

            // --- Service : doublons de label autorisés ---
            // On rend la clé PHP unique en suffixant avec l'id de ligne
            $serviceLabel = $item['service_code'] . ' ' . $item['service_libelle'];
            $serviceChoices[$serviceLabel . '_' . $id] = $id;

            // --- Attributs HTML de l'option service ---
            // data-agence permet de filtrer les services par agence côté JS
            $serviceAttr[$id] = [
                'data-agence' => $item['agence_id'],
            ];
        }

        return [
            'agenceChoices'  => $agenceChoices,  // array<label, agence_id>
            'serviceChoices' => $serviceChoices, // array<label_unique, id_ligne>
            'serviceAttr'    => $serviceAttr,    // array<id_ligne, ['data-agence' => agence_id]>
        ];
    }
}
