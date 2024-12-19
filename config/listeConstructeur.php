<?php

use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use App\Service\fichier\JsonFileService;
// Exemple d'utilisation de la classe

try {

    $chemin = 'C:\wamp64\www\Upload\variable_global/liste_constructeur.json';
    $jsonService = new JsonFileService($chemin);

    // Récupérer une section spécifique
    GlobalVariablesService::set('pieces_magasin', TableauEnStringService::orEnString($jsonService->getSection("PIECES MAGASIN")));
    GlobalVariablesService::set('achat_locaux', TableauEnStringService::orEnString($jsonService->getSection('ACHATS LOCAUX')));
    GlobalVariablesService::set('lub', TableauEnStringService::orEnString($jsonService->getSection('LUB')));

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}