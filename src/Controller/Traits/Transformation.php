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
            if(is_array($values)){
                foreach ($values as $value) {
                    $tab[] = $value;
                }
            } else {
                $tab[] = $values;
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

    public function transformEnSeulTableauAvecKeyService(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as   $values) {
           
            $tab[$values['text']] = $values['value'];
        }
        return $tab;
    }
}
