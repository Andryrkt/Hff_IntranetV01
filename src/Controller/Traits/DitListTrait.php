<?php

namespace App\Controller\Traits;

trait DitListTrait
{
    /**
 * RECUPERATION DE L'ID MATERIEL EN CHAINE DE CARACTERE
 *
 * @param array $data
 * @return string
 */
private function recupIdMaterielEnChaine(array $data): string
{
    $idMateriels = '(';
    foreach ($data as $value) {
        $idMateriels .= $value->getIdMateriel() . ',';
    }
  $idMateriels .= ')';
  $idMateriels = substr_replace($idMateriels, '', strrpos($idMateriels, ','), 1);
  return $idMateriels;
}
}