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
}
