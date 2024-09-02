<?php

namespace App\Controller\Traits;

trait Transformation
{
    /**
     * transforme en une seul tableau
     */
    public function transformEnSeulTableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as  $values) {
            foreach ($values as $value) {
                $tab[] = $value;
            }
        }

        return $tab;
    }

    public function transformEnSeulTableauAvecKey(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as   $values) {
            foreach ($values as $key =>$value) {
                $tab[$key] = $value;
            }
        }

        return $tab;
    }
}
