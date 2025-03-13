<?php

namespace App\Traits;

trait CalculeTrait
{
    private function calculeMarge($montantAp, $montantAv) {
        if($montantAv <> 0 ) {
            return round((($montantAp - $montantAv)/$montantAv)*100);
        } else {
            $message = " Erreur lors de la soumission, Impossible de soumettre le devis . . . le {$montantAv} doit être différent de zero";
            $this->historiqueOperation->sendNotificationSoumission($message, $montantAv, 'dit_index');
        }
    }
}
