<?php

namespace App\Controller\Traits;

trait lienGenerique
{

    /** 
     * Génère un lien complet basé sur le domaine ou l'adresse IP actuelle.
     * 
     * Cette fonction utilise l'hôte courant (localhost, IP ou domaine) pour construire un lien complet.
     * 
     * @param string $url l'url à générer sans le domaine ou l'adresse IP (Exemple)
     * @return string Le lien complet vers le projet.
     * 
     * ```php
     * // Exemple d'utilisation :
     * $link = generateLink("my-project");
     * echo $link; // Résultat possible : http://localhost/my-project
     * ```
     */
    private function urlGenerique(string $url): string
    {
        $host = $_SERVER['HTTP_HOST']; // Récupère l'IP ou le domaine courant
        return "http://$host/$url";
    }
}
