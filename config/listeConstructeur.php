<?php

use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use App\Service\fichier\JsonFileService;
use Symfony\Component\Finder\Glob;

// Exemple d'utilisation de la classe

try {

    ;
    $jsonService = new JsonFileService();

    //CHEMIN DE BASE
    $cheminBaseLong = $jsonService->getSection("CHEMIN DE BASE LONG") ?? "";
    $cheminBaseCourt = $jsonService->getSection("CHEMIN DE BASE COURT") ?? "";
    $cheminUploadFile = $jsonService->getSection("CHEMIN DE STOCKAGE FICHIER") ?? "";
    $cheminDw = $jsonService->getSection("CHEMIN VERS DOCUWARE") ?? "";
    $cheminLog = $jsonService->getSection("CHEMIN LOG") ?? "";

    GlobalVariablesService::set('chemin_base_long', $cheminBaseLong);
    GlobalVariablesService::set('chemin_base_court', $cheminBaseCourt);
    GlobalVariablesService::set('chemin_upload_file', $cheminUploadFile);
    GlobalVariablesService::set('chemin_dw', $cheminDw);
    GlobalVariablesService::set('chemin_log', $cheminLog);


    /**===================
     * CONSTRUCTEUR PIECES
     *=====================*/  
    $pieceMagasin = $jsonService->getSection("PIECES MAGASIN") === null ? [] : $jsonService->getSection("PIECES MAGASIN");
    $achatsLocaux = $jsonService->getSection('ACHATS LOCAUX') === null ? [] : $jsonService->getSection('ACHATS LOCAUX');
    $lub = $jsonService->getSection('LUB') === null ? [] : $jsonService->getSection('LUB');
    $tous = $jsonService->getSection('TOUS') === null ? [] : $jsonService->getSection('TOUS');

    // Récupérer une section spécifique
    GlobalVariablesService::set('pieces_magasin', TableauEnStringService::orEnString($pieceMagasin));
    GlobalVariablesService::set('achat_locaux', TableauEnStringService::orEnString($achatsLocaux));
    GlobalVariablesService::set('lub', TableauEnStringService::orEnString($lub));
    GlobalVariablesService::set('tous', TableauEnStringService::orEnString($tous));

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}