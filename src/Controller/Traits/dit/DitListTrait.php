<?php

namespace App\Controller\Traits\dit;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\dit\CategorieAteApp;

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
          ->setDitSansOr($form->get('ditSansOr')->getData())
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
    private function initialisationRechercheDit($ditSearch, $em, $agenceServiceIps, $autoriser)
    {
      
        $criteria = $this->sessionService->get('dit_search_criteria', []);
        
        if($criteria !== null){
            if ($autoriser) {
                $agenceIpsEmetteur = null;
                $serviceIpsEmetteur = null;
            } else {
                $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
                $serviceIpsEmetteur = $agenceServiceIps['serviceIps'];
            }
            $typeDocument = $criteria['typeDocument'] === null ? null : $em->getRepository(WorTypeDocument::class)->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $em->getRepository(WorNiveauUrgence::class)->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
            $serviceEmetteur = $criteria['serviceEmetteur'] === null ? $serviceIpsEmetteur : $em->getRepository(Service::class)->find($criteria['serviceEmetteur']->getId());
            $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceDebiteur']->getId());
            $agenceEmetteur = $criteria['agenceEmetteur'] === null ? $agenceIpsEmetteur : $em->getRepository(Agence::class)->find($criteria['agenceEmetteur']->getId());
            $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $em->getRepository(Agence::class)->find($criteria['agenceDebiteur']->getId());
            $categorie = $criteria['categorie'] === null ? null : $em->getRepository(CategorieAteApp::class)->find($criteria['categorie']);
        } else {
            if ($autoriser) {
                $agenceIpsEmetteur = null;
                $serviceIpsEmetteur = null;
            } else {
                $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
                $serviceIpsEmetteur = $agenceServiceIps['serviceIps'];
            }
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $agenceEmetteur = $agenceIpsEmetteur;
            $serviceEmetteur = $serviceIpsEmetteur;
            $serviceDebiteur = null;
            $agenceDebiteur = null;
            $categorie = null;
        }

      $ditSearch
        ->setStatut($statut)
        ->setNiveauUrgence($niveauUrgence)
        ->setTypeDocument($typeDocument)
        ->setInternetExterne($criteria['interneExterne'] ?? null)
        ->setDateDebut($criteria['dateDebut'] ?? null)
        ->setDateFin($criteria['dateFin'] ?? null)
        ->setIdMateriel($criteria['idMateriel'] ?? null)
        ->setNumParc($criteria['numParc'] ?? null)
        ->setNumSerie($criteria['numSerie'] ?? null)
        ->setAgenceEmetteur($agenceEmetteur)
        ->setServiceEmetteur($serviceEmetteur)
        ->setAgenceDebiteur($agenceDebiteur)
        ->setServiceDebiteur($serviceDebiteur)
        ->setNumDit($criteria['numDit'] ?? null)
        ->setNumOr($criteria['numOr'] ?? null)
        ->setStatutOr($criteria['statutOr'] ?? null)
        ->setDitSansOr($criteria['ditSansOr'] ?? null)
        ->setCategorie($categorie)
        ->setUtilisateur($criteria['utilisateur'] ?? null)
        ->setSectionAffectee($criteria['sectionAffectee'] ?? null)
        ->setSectionSupport1($criteria['sectionSupport1'] ?? null)
        ->setSectionSupport2($criteria['sectionSupport2'] ?? null)
        ->setSectionSupport3($criteria['sectionSupport3'] ?? null)
        ;

    } 

    private function agenceServiceEmetteur($agenceServiceIps, bool $autoriser): array
    {

        //initialisation agence et service
        if($autoriser){
            $agence = null;
            $service = null;
        } else {
            $agence = $agenceServiceIps['agenceIps'];
            $service = $agenceServiceIps['serviceIps'];
        }

        return [
            'agence' => $agence,
            'service' => $service
        ];
    }

    private function ajoutNumSerieNumParc($data)
    {
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                // Associez chaque entité à ses valeurs de num_serie et num_parc
                $data[$i]->setNumSerie($this->ditModel->recupNumSerieParc($data[$i]->getIdMateriel())[0]['num_serie']);
                $data[$i]->setNumParc($this->ditModel->recupNumSerieParc($data[$i]->getIdMateriel())[0]['num_parc']);
            }
    }
    }

    private function ajoutStatutAchatPiece($data){
        for ($i=0 ; $i < count($data) ; $i++ ) { 
            if ($data[$i]->getNumeroOR() !== null) {
                if(!empty($this->ditModel->recupQuantite($data[$i]->getNumeroOR()))) {
                    foreach ($this->ditModel->recupQuantite($data[$i]->getNumeroOR()) as $value) {
                        $data[$i]->setQuantiteDemander($value['quantitedemander']);
                        $data[$i]->setQuantiteReserver($value['quantitereserver']);
                        $data[$i]->setQuantiteLivree($value['quantitelivree']);
                    }
                    if($data[$i]->getQuantiteLivree() === 0 || $data[$i]->getQuantiteLivree() === null){
                        $data[$i]->setStatutAchatPiece("En cours");
                    } elseif ($data[$i]->getQuantiteLivree() < $data[$i]->getQuantiteDemander()) {
                        $data[$i]->setStatutAchatPiece("Livré partiellement");
                    } elseif ($data[$i]->getQuantiteLivree() === $data[$i]->getQuantiteDemander()) {
                        $data[$i]->setStatutAchatPiece("Livré totalement");
                    }
                }
            }
        }
    }

    private function ajoutStatutAchatLocaux($data){
        for ($i=0 ; $i < count($data) ; $i++ ) { 
            if ($data[$i]->getNumeroOR() !== null) {
                if(!empty($this->ditModel->recupQuantiteStatutAchatLocaux($data[$i]->getNumeroOR()))) {
                    foreach ($this->ditModel->recupQuantiteStatutAchatLocaux($data[$i]->getNumeroOR()) as $value) {
                        $data[$i]->setQuantiteDemander($value['quantitedemander']);
                        $data[$i]->setQuantiteReserver($value['quantitereserver']);
                        $data[$i]->setQuantiteLivree($value['quantitelivree']);
                    }
                    if($data[$i]->getQuantiteLivree() === 0 || $data[$i]->getQuantiteLivree() === null){
                        $data[$i]->setStatutAchatLocaux("En cours");
                    } elseif ($data[$i]->getQuantiteLivree() < $data[$i]->getQuantiteDemander()) {
                        $data[$i]->setStatutAchatLocaux("Livré partiellement");
                    } elseif ($data[$i]->getQuantiteLivree() === $data[$i]->getQuantiteDemander()) {
                        $data[$i]->setStatutAchatLocaux("Livré totalement");
                    }
                }
            }
        }
    }

    private function ajoutNbrPj($data, $em){
        for ($i=0 ; $i < count($data) ; $i++ ) { 
            $data[$i]->setNbrPj($em->getRepository(DemandeIntervention::class)->findNbrPj($data[$i]->getNumeroDemandeIntervention()));
        }
    }


    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->sessionService->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds) || in_array(4, $roleIds);
    }


    private function ajoutQuatreStatutOr($data){
        for ($i = 0; $i < count($data); $i++) { 
            if ($data[$i]->getNumeroOR() !== null) {
                // Initialisation des valeurs avant de les utiliser
                $data[$i]->setQuantiteDemander(0);
                $data[$i]->setQuantiteReserver(0);
                $data[$i]->setQuantiteLivree(0);
                
                $quantites = $this->ditModel->recupQuantiteQuatreStatutOr($data[$i]->getNumeroOR());
                if (!empty($quantites)) {
                    foreach ($quantites as $value) {
                        $data[$i]->setQuantiteDemander((int)$value['quantitedemander']);
                        $data[$i]->setQuantiteReserver((int)$value['quantitereserver']);
                        $data[$i]->setQuantiteLivree((int)$value['qteliv']);
                    }
                    
                    // Définition des conditions
                    $quantiteDemander = (int)$data[$i]->getQuantiteDemander();
                    $quantiteReserver = (int)$data[$i]->getQuantiteReserver();
                    $quantiteLivree = (int)$data[$i]->getQuantiteLivree();
                    
                    $conditionToutLivre = $quantiteDemander === $quantiteLivree && $quantiteDemander !== 0 && $quantiteLivree !== 0;
                    $conditionPartiellementLivre = $quantiteLivree > 0 && $quantiteLivree !== $quantiteDemander && $quantiteDemander !== 0;
                    $conditionPartiellementDispo = $quantiteReserver !== $quantiteDemander && ($quantiteLivree === 0 || $quantiteLivree === null) && $quantiteReserver > 0;
                    $conditionCompletNonLivre = $quantiteDemander == $quantiteReserver && $quantiteLivree < $quantiteDemander;
                    
                    // Définition du statut basé sur les conditions
                    if ($conditionToutLivre) {
                        $data[$i]->setQuatreStatutOr('Tout livré');
                    } elseif ($conditionPartiellementLivre) {
                        $data[$i]->setQuatreStatutOr('Partiellement livré');
                    } elseif ($conditionPartiellementDispo) {
                        $data[$i]->setQuatreStatutOr('Partiellement dispo');
                    } elseif ($conditionCompletNonLivre) {
                        $data[$i]->setQuatreStatutOr('Complet non livré');
                    } else {
                        $data[$i]->setQuatreStatutOr('');
                    }
                    
                }
            }
        }
    }

    private function estNumorEqNumDit($numDit)
    {
        $nbNumor = $this->ditModel->recupNbNumor($numDit);
        $estRelier = false;
        if(!empty($nbNumor) && $nbNumor[0]['nbor'] !== "0")
        {
            $estRelier = true;
        }

        return $estRelier;
    }

    private function ajoutConditionOrEqDit($data)
    {
        
        for ($i=0; $i < count($data); $i++) {
            $estOrEqDit = $this->estNumorEqNumDit($data[$i]->getNumeroDemandeIntervention());
           $data[$i]->setEstOrEqDit($estOrEqDit);
        }
       
    }
}