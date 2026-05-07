<?php

namespace App\Controller\Traits\ddp;

trait DocDdpTrait
{
    private function nomFichier(string $cheminFichier): string
    {
        $motExacteASupprimer = [
            '\\\\192.168.0.15',
            '\\GCOT_DATA',
            '\\TRANSIT',
        ];

        $motCommenceASupprimer = ['\\DD'];

        return $this->enleverPartiesTexte($cheminFichier, $motExacteASupprimer, $motCommenceASupprimer);
    }

    private function enleverPartiesTexte(string $texte, array $motsExacts, array $motsCommencent): string
    {
        // Supprimer les correspondances exactes
        foreach ($motsExacts as $mot) {
            $texte = str_replace($mot, '', $texte);
        }

        // Supprimer les parties qui commencent par un mot donné
        foreach ($motsCommencent as $motDebut) {
            $pattern = '/' . preg_quote($motDebut, '/') . '[^\\\\]*/';
            $texte = preg_replace($pattern, '', $texte);
        }

        // Supprimer les éventuels slashes de début
        return ltrim($texte, '\\/');
    }
}
