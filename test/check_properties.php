<?php

require_once __DIR__ . '/../vendor/autoload.php';

$reflection = new ReflectionClass('App\Service\genererPdf\GeneratePdf');
echo "Propriétés de GeneratePdf:\n";
foreach ($reflection->getProperties() as $prop) {
    echo "- " . $prop->getName() . "\n";
}

$reflection2 = new ReflectionClass('App\Service\genererPdf\GeneratePdfDevisMagasin');
echo "\nPropriétés de GeneratePdfDevisMagasin:\n";
foreach ($reflection2->getProperties() as $prop) {
    echo "- " . $prop->getName() . "\n";
}
