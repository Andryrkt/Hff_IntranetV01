<?php

namespace App\Traits;

trait CalculeTrait
{
    private function calculeMarge($montantAp, $montantAv) {
        if($montantAv <> 0 ) {
            return round((($montantAp - $montantAv)/$montantAv)*100);
        } else {
            return 0;
        }
    }
}
