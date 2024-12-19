<?php

use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use App\Service\fichier\JsonFileService;
// Exemple d'utilisation de la classe

try {

    $chemin = 'C:\wamp64\www\Upload\variable_global/liste_constructeur.json';
    $jsonService = new JsonFileService($chemin);

    $pieceMagasin = $jsonService->getSection("PIECES MAGASIN") === null ? [] : $jsonService->getSection("PIECES MAGASIN");
    $achatsLocaux = $jsonService->getSection('ACHATS LOCAUX') === null ? [] : $jsonService->getSection('ACHATS LOCAUX');
    $lub = $jsonService->getSection('LUB') === null ? [] : $jsonService->getSection('LUB');

    // Récupérer une section spécifique
    GlobalVariablesService::set('pieces_magasin', TableauEnStringService::orEnString($pieceMagasin));
    GlobalVariablesService::set('achat_locaux', TableauEnStringService::orEnString($achatsLocaux));
    GlobalVariablesService::set('lub', TableauEnStringService::orEnString($lub));

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}