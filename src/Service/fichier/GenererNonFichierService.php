<?php

namespace App\Service\fichier;

class GenererNonFichierService
{
    public function genererNonFichier(string $prefix = '', string $numeroDoc, string $index, string $numeroVersion = ''): string
    {
        return sprintf(
            '%s_%s%s%s.%s',
            $prefix,
            $numeroDoc,
            $index,
            $numeroVersion,
            'pdf'
        );
    }
}