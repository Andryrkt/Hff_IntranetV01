<?php

namespace App\Controller\Traits;

trait BadmsForm2Trait
{
    private function changeEtatAchat($dataEtatAchat)
    {
        if ($dataEtatAchat === 'N') {
            return 'Neuf';
        } else {
            return 'Occasion';
        }
    }
}