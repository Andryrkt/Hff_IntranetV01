<?php

use App\Service\SessionManagerService;
use App\Service\fichier\JsonFileService;
// Exemple d'utilisation de la classe

try {
    $sessionService = new SessionManagerService();
    $jsonService = new JsonFileService('donnees.json');

    // Récupérer une section spécifique
    $piecesMagasin = $jsonService->getSection("PIECES MAGASIN");
    $sessionService->set('pieces_magasin', $piecesMagasin);
    $achatLocaux = $jsonService->getSection('ACHATS LOCAUX');
    $sessionService->set('achat_locaux', $achatLocaux);
    $lub = $jsonService->getSection('LUB');
    $sessionService->set('lub', $lub);

    // Ajouter un nouvel élément à la section PIECES MAGASIN
    //$service->addElementToSection("PIECES MAGASIN", "NOUVEAU_ELEMENT");
    
    // Sauvegarder les changements
    $jsonService->save();

    // Affichage de la section mise à jour
    // echo "<pre>";
    // print_r($service->getAllData());
    // echo "</pre>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}