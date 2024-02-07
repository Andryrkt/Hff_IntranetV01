<?php
$fichier = $_GET['pdf'];
//echo $fichier;
//$file = "../../../Backend/pdf/".$fichier;
$file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf'.$fichier;
header("Content-type: application/pdf"); 
    
header("Content-Length: " . filesize($file)); 
  
// Envoyez le fichier au navigateur.
readfile($file); 
