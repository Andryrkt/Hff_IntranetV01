<?php

use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use App\Service\fichier\JsonFileService;
use Symfony\Component\Finder\Glob;

// Exemple d'utilisation de la classe

try {

    $chemin = 'C:\wamp64\www\Upload\variable_global/liste_constructeur.json';
    $jsonService = new JsonFileService($chemin);

    /**===================
     * CONSTRUCTEUR PIECES
     *=====================*/  
    $pieceMagasin = $jsonService->getSection("PIECES MAGASIN") === null ? [] : $jsonService->getSection("PIECES MAGASIN");
    $achatsLocaux = $jsonService->getSection('ACHATS LOCAUX') === null ? [] : $jsonService->getSection('ACHATS LOCAUX');
    $lub = $jsonService->getSection('LUB') === null ? [] : $jsonService->getSection('LUB');
    $tous = $jsonService->getSection('TOUS') === null ? [] : $jsonService->getSection('TOUS');
    $pieceMagasinSansCat = $jsonService->getSection('PIECES MAGASIN SANS CAT') === null ? [] : $jsonService->getSection('PIECES MAGASIN SANS CAT');

    // Récupérer une section spécifique
    GlobalVariablesService::set('pieces_magasin', TableauEnStringService::orEnString($pieceMagasin));
    GlobalVariablesService::set('achat_locaux', TableauEnStringService::orEnString($achatsLocaux));
    GlobalVariablesService::set('lub', TableauEnStringService::orEnString($lub));
    GlobalVariablesService::set('tous', TableauEnStringService::orEnString($tous));
    GlobalVariablesService::set('pieceMagasinSansCat', TableauEnStringService::orEnString($pieceMagasinSansCat));

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}