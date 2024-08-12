<?php

namespace App\Controller\Traits;

use App\Entity\StatutDemande;
use App\Entity\WorTypeDocument;
use App\Entity\WorNiveauUrgence;
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
          ->setNumDit($form->get('numDit')->getData())
          ->setNumOr($form->get('numOr')->getData())
          ->setStatutOr($form->get('statutOr')->getData())
          ->setDitRattacherOr($form->get('ditRattacherOr')->getData())
          ->setCategorie($form->get('categorie')->getData())
          ->setUtilisateur($form->get('utilisateur')->getData())
          ;
          $this->ajoutAgenceServiceDebiteur($form, $ditSearch);
        
    }



    /**
     * function pour l'initialisation des donners
     *
     * @param [type] $ditSearch
     * @param [type] $em
     * @param [type] $request
     * @param [type] $agence
     * @param [type] $service
     * @return void
     */
    private function initialisationRechercheDit($ditSearch, $em, $request, $agence, $service)
    {
      if($request->query->get('page') !== null){
        if($request->query->get('typeDocument') !== null){
            $idTypeDocument = $em->getRepository(WorTypeDocument::class)->findBy(['description' => $request->query->get('typeDocument')], [])[0]->getId();
            $typeDocument = $em->getRepository(WorTypeDocument::class)->find($idTypeDocument) ;
        } else {
            $typeDocument = $request->query->get('typeDocument', null);
        }

        if($request->query->get('niveauUrgence') !== null){
            $idNiveauUrgence = $em->getRepository(WorNiveauUrgence::class)->findBy(['description' => $request->query->get('niveauUrgence')], [])[0]->getId();
            
            $niveauUrgence = $em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence) ;
        } else {
            $niveauUrgence = $request->query->get('niveauUrgence', null);
        }
       
        if($request->query->get('statut') !== null){
            $idStatut = $em->getRepository(StatutDemande::class)->findBy(['description' => $request->query->get('statut')], [])[0]->getId();
            $statut = $em->getRepository(StatutDemande::class)->find($idStatut) ;
        } else {
            $statut = $request->query->get('statut', null);
        }
        
      } else {
        $typeDocument = $request->query->get('typeDocument', null);
        $niveauUrgence = $request->query->get('niveauUrgence', null);
        $statut = $request->query->get('statut', null);
        
        
        if($request->query->get('dit_search') !== null) {
            if($request->query->get('dit_search')['typeDocument'] !== null){
                $idTypeDocument = $request->query->get('dit_search')['typeDocument'];
                $typeDocument = $em->getRepository(WorTypeDocument::class)->find($idTypeDocument);
            } else {
                $typeDocument = $request->query->get('typeDocument', null);
            }

            if($request->query->get('dit_search')['niveauUrgence'] !== null){
                $idNiveauUrgence = $request->query->get('dit_search')['niveauUrgence'];
                
                $niveauUrgence = $em->getRepository(WorNiveauUrgence::class)->find($idNiveauUrgence);
            } else {
                $niveauUrgence = $request->query->get('niveauUrgence', null);
            }
            
            if($request->query->get('dit_search')['statut'] !== null){
                $idStatut = $request->query->get('dit_search')['statut'];
                $statut = $em->getRepository(StatutDemande::class)->find($idStatut);
            } else {
                $statut = $request->query->get('statut', null);
            }
        
        } else {
            $typeDocument = $request->query->get('typeDocument', null);
            $niveauUrgence = $request->query->get('niveauUrgence', null);
            $statut = $request->query->get('statut', null);
        }
      }

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