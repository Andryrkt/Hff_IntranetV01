<?php

namespace App\Controller\Traits;

use App\Entity\DemandeIntervention;

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

    /**
     * RECUPERATION des DONNEES DE RECHERCHE agence et service debiteur
     *
     * @param [type] $form
     * @param [type] $ditSearch
     * @return void
     */
    private function ajoutAgenceServiceDebiteur($form, $ditSearch)
    {
        if ($form->get('agenceDebiteur')->getData() === null  && $form->get('serviceDebiteur')->getData() === null) {
          $ditSearch
          ->setAgenceDebiteur(null)
          ->setServiceDebiteur(null);
      } else {
          $ditSearch
          ->setAgenceDebiteur($form->get('agenceDebiteur')->getData())
          ->setServiceDebiteur($form->get('serviceDebiteur')->getData());
      }
    }

    /**
     * Ajout de donner de recherche sans condition, id materiel, agence service debiteur dans l'objet ditSearch
     *
     * @param [type] $form
     * @param [type] $ditSearch
     * @return void
     */
    private function ajoutDonnerRecherche($form, $ditSearch)
    {
      $ditSearch
          ->setStatut($form->get('statut')->getData())
          ->setNiveauUrgence($form->get('niveauUrgence')->getData())
          ->setTypeDocument($form->get('typeDocument')->getData())
          ->setInternetExterne($form->get('internetExterne')->getData())
          ->setDateDebut($form->get('dateDebut')->getData())
          ->setDateFin($form->get('dateFin')->getData())
          ->setAgenceEmetteur($form->get('agenceEmetteur')->getData())
          ->setServiceEmetteur($form->get('serviceEmetteur')->getData())
          ;
    }

   


    private function initialisationRechercheDit($ditSearch, $statut, $niveauUrgence, $typeDocument, $request, $agence, $service)
    {
      $ditSearch
        ->setStatut($statut)
        ->setNiveauUrgence($niveauUrgence)
        ->setTypeDocument($typeDocument)
        ->setIdMateriel($request->query->get('idMateriel'))
        ->setInternetExterne($request->query->get('internetExterne'))
        ->setDateDebut($request->query->get('dateDebut'))
        ->setDateFin($request->query->get('dateFin'))
        ->setAgenceEmetteur($agence)
        ->setServiceEmetteur($service)
        ;
    } 

}