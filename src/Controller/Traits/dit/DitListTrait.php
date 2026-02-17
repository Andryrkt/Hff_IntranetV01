<?php

namespace App\Controller\Traits\dit;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dit\DitSearch;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\CategorieAteApp;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\utilisateur\Role;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;

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
     * @return void
     */
    private function initialisationRechercheDit($ditSearch, $em)
    {

        $criteria = $this->getSessionService()->get('dit_search_criteria', []);
        if (!empty($criteria)) {
            $typeDocument = $criteria['typeDocument'] === null ? null : $em->getRepository(WorTypeDocument::class)->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $em->getRepository(WorNiveauUrgence::class)->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
            $serviceEmetteur = $criteria['serviceEmetteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceEmetteur']->getId());
            $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceDebiteur']->getId());
            $agenceEmetteur = $criteria['agenceEmetteur'] === null ? null : $em->getRepository(Agence::class)->find($criteria['agenceEmetteur']->getId());
            $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $em->getRepository(Agence::class)->find($criteria['agenceDebiteur']->getId());
            $categorie = $criteria['categorie'] === null ? null : $em->getRepository(CategorieAteApp::class)->find($criteria['categorie']);
        } else {
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $agenceEmetteur = null;
            $serviceEmetteur = null;
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
            ->setEtatFacture($criteria['etatFacture'] ?? null)
        ;
    }

    private function agenceServiceEmetteur($agenceServiceIps, bool $autoriser): array
    {

        //initialisation agence et service
        if ($autoriser) {
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
                if (!empty($data[$i]->getIdMateriel())) {

                    // Associez chaque entité à ses valeurs de num_serie et num_parc
                    $numSerieParc = $this->ditModel->recupNumSerieParc($data[$i]->getIdMateriel());
                    if (!empty($numSerieParc)) {
                        $numSerie = $numSerieParc[0]['num_serie'];
                        $numParc = $numSerieParc[0]['num_parc'];
                        $data[$i]->setNumSerie($numSerie);
                        $data[$i]->setNumParc($numParc);
                    } else {
                        $data[$i]->setNumSerie('');
                        $data[$i]->setNumParc('');
                    }
                }
            }
        }
    }

    private function ajoutMarqueCasierMateriel($data)
    {
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                if (!empty($data[$i]->getIdMateriel())) {
                    // Associez chaque entité à ses valeurs de num_serie et num_parc
                    $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($data[$i]->getIdMateriel());
                    if (!empty($marqueCasier)) {
                        $marque = $marqueCasier[0]['marque'];
                        $casier = $marqueCasier[0]['casier'];
                        $data[$i]->setMarque($marque);
                        $data[$i]->setCasier($casier);
                    } else {
                        $data[$i]->setMarque('');
                        $data[$i]->setCasier('');
                    }
                }
            }
        }
    }

    private function ajoutStatutAchatPiece($data)
    {
        for ($i = 0; $i < count($data); $i++) {

            if ($data[$i]->getNumeroOR() !== null && $data[$i]->getNumeroOR() !== 'NULL') {

                if (!empty($this->ditModel->recupQuantite($data[$i]->getNumeroOR()))) {

                    foreach ($this->ditModel->recupQuantite($data[$i]->getNumeroOR()) as $value) {
                        $data[$i]->setQuantiteDemander($value['quantitedemander']);
                        $data[$i]->setQuantiteReserver($value['quantitereserver']);
                        $data[$i]->setQuantiteLivree($value['quantitelivree']);
                    }
                    if ($data[$i]->getQuantiteLivree() === 0 || $data[$i]->getQuantiteLivree() === null) {
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

    private function ajoutStatutAchatLocaux($data)
    {
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]->getNumeroOR() !== null) {
                if (!empty($this->ditModel->recupQuantiteStatutAchatLocaux($data[$i]->getNumeroOR()))) {
                    foreach ($this->ditModel->recupQuantiteStatutAchatLocaux($data[$i]->getNumeroOR()) as $value) {
                        $data[$i]->setQuantiteDemander($value['quantitedemander']);
                        $data[$i]->setQuantiteReserver($value['quantitereserver']);
                        $data[$i]->setQuantiteLivree($value['quantitelivree']);
                    }
                    if ($data[$i]->getQuantiteLivree() === 0 || $data[$i]->getQuantiteLivree() === null) {
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

    private function ajoutNbrPj($data, $em)
    {
        for ($i = 0; $i < count($data); $i++) {
            $nbrJr = $em->getRepository(DemandeIntervention::class)->findNbrPj($data[$i]->getNumeroDemandeIntervention());
            $data[$i]->setNbrPj($nbrJr);
        }
    }

    private function ajoutQuatreStatutOr($data)
    {
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
        if (!empty($nbNumor) && $nbNumor[0]['nbor'] !== "0") {
            $estRelier = true;
        }

        return $estRelier;
    }

    private function ajoutConditionOrEqDit($data)
    {
        for ($i = 0; $i < count($data); $i++) {
            $estOrEqDit = $this->estNumorEqNumDit($data[$i]->getNumeroDemandeIntervention());
            $data[$i]->setEstOrEqDit($estOrEqDit);
        }
    }

    private function ajoutri($data, $ditListeModel, $em)
    {
        foreach ($data as $value) {
            $itvSoumisRi = $em->getRepository(DitRiSoumisAValidation::class)->findNbreNumItv($value->getNumeroOR())[0];
            $itvTotal = $ditListeModel->recupNbItv($value->getNumeroOR());

            // Mise à jour de la propriété 'ri'
            $value->setRi($itvSoumisRi . "/" . $itvTotal);

            // Persist l'entité après modification
            $em->persist($value);
        }
        // Sauvegarde des changements dans la base de données
        $em->flush();
    }

    private function orEnString($tab): string
    {
        $numOrValide = $this->transformEnSeulTableau($tab);

        return implode("','", $numOrValide);
    }

    private function transformEnSeulTableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as  $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $tab[] = $value;
                }
            } else {
                $tab[] = $values;
            }
        }

        return $tab;
    }


    private function donnerAAfficher($ditListeModel, $ditSearch, $option, $page, $limit, $em)
    {
        $paginationData = $em->getRepository(DemandeIntervention::class)->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);

        //ajout de donner du statut achat piece dans data
        $this->ajoutStatutAchatPiece($paginationData['data']);

        //ajout de donner du statut achat locaux dans data
        $this->ajoutStatutAchatLocaux($paginationData['data']);

        //ajout nombre de pièce joint
        $this->ajoutNbrPj($paginationData['data'], $em);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data']);

        $this->ajoutQuatreStatutOr($paginationData['data']);

        $this->ajoutConditionOrEqDit($paginationData['data']);

        $this->ajoutri($paginationData['data'], $ditListeModel, $em);

        $this->ajoutMarqueCasierMateriel($paginationData['data']);

        return $paginationData;
    }

    // private function dossierDit($request, $formDocDansDW)
    // {

    //     $formDocDansDW->handleRequest($request);

    //     if($formDocDansDW->isSubmitted() && $formDocDansDW->isValid()) {
    //         if($formDocDansDW->getData()['docDansDW'] === 'OR'){
    //             $this->redirectToRoute("dit_insertion_or", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
    //         } else if($formDocDansDW->getData()['docDansDW'] === 'FACTURE'){
    //             $this->redirectToRoute("dit_insertion_facture", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
    //         } elseif ($formDocDansDW->getData()['docDansDW'] === 'RI') {
    //             $this->redirectToRoute("dit_insertion_ri", ['numDit' => $formDocDansDW->getData()['numeroDit']]);
    //         }
    //     } 
    // }

    private function Option($autoriser, $autorisationRoleEnergie, $agenceServiceEmetteur, $agenceIds, $serviceIds): array
    {
        return  [
            'boolean' => $autoriser,
            'autorisationRoleEnergie' => $autorisationRoleEnergie,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getId(),
            'agenceAutoriserIds' => $agenceIds,
            'serviceAutoriserIds' => $serviceIds,
            'user_agency' => $this->getUser()->getCodeAgenceUser(),
            // 'InternetExterne' => 
            //'codeService' =>$agenceServiceEmetteur['service'] === null ? null : $agenceServiceEmetteur['service']->getCodeService()
        ];
    }

    private function transformationEnObjet(array $criteria)
    {
        $ditSearch = new DitSearch();
        $ditSearch
            ->setTypeDocument($criteria["typeDocument"])
            ->setNiveauUrgence($criteria["niveauUrgence"])
            ->setStatut($criteria["statut"])
            ->setInternetExterne($criteria["interneExterne"])
            ->setDateDebut($criteria["dateDebut"])
            ->setDateFin($criteria["dateFin"])
            ->setIdMateriel($criteria["idMateriel"])
            ->setNumParc($criteria["numParc"])
            ->setNumSerie($criteria["numSerie"])
            ->setAgenceEmetteur($criteria["agenceEmetteur"])
            ->setServiceEmetteur($criteria["serviceEmetteur"])
            ->setAgenceDebiteur($criteria["agenceDebiteur"])
            ->setServiceDebiteur($criteria["serviceDebiteur"])
            ->setNumDit($criteria["numDit"])
            ->setNumOr($criteria["numOr"])
            ->setStatutOr($criteria["statutOr"])
            ->setDitSansOr($criteria["ditSansOr"])
            ->setCategorie($criteria["categorie"])
            ->setUtilisateur($criteria["utilisateur"])
            ->setDitSansOr($criteria["ditSansOr"])
            ->setSectionAffectee($criteria["sectionAffectee"])
            ->setSectionSupport1($criteria["sectionSupport1"])
            ->setSectionSupport2($criteria["sectionSupport2"])
            ->setSectionSupport3($criteria["sectionSupport3"])
            ->setEtatFacture(($criteria['etatFacture']))
        ;

        return $ditSearch;
    }

    private function DonnerAAjouterExcel(DitSearch $ditSearch, $options, $em): array
    {
        $entities = $em->getrepository(DemandeIntervention::class)->findAndFilteredExcel($ditSearch, $options);

        $this->ajoutStatutAchatPiece($entities);

        $this->ajoutStatutAchatLocaux($entities);

        $this->ajoutNbrPj($entities, $em);

        $this->ajoutNumSerieNumParc($entities);

        $this->ajoutMarqueCasierMateriel($entities);

        return $entities;
    }

    private function transformationEnTableauAvecEntet($entities): array
    {
        $data = [];
        $data[] = ['Statut', 'N° DIT', 'Réalisé par', 'Type Document', 'Niveau', 'Catégorie de Demande', 'N°Serie', 'N°Parc', 'date demande', 'Int/Ext', 'Emetteur', 'Débiteur',  'Objet', 'sectionAffectee', 'N° devis', 'Statut Devis', 'N°Or', 'Statut Or', 'Statut facture', 'RI', 'Nbre Pj', 'utilisateur', 'Marque', 'Casier']; // En-têtes des colonnes

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getIdStatutDemande()->getDescription(),
                $entity->getNumeroDemandeIntervention(),
                $entity->getReparationRealise(),
                $entity->getTypeDocument()->getDescription(),
                $entity->getIdNiveauUrgence()->getDescription(),
                $entity->getCategorieDemande()->getLibelleCategorieAteApp(),
                $entity->getNumSerie(),
                $entity->getNumParc(),
                $entity->getDateDemande(),
                $entity->getInternetExterne(),
                $entity->getAgenceServiceEmetteur(),
                $entity->getAgenceServiceDebiteur(),
                $entity->getObjetDemande(),
                $entity->getSectionAffectee(),
                $entity->getNumeroDevisRattache(),
                $entity->getStatutDevis(),
                $entity->getNumeroOr(),
                $entity->getStatutOr(),
                $entity->getEtatFacturation(),
                $entity->getRi(),
                $entity->getNbrPj(),
                $entity->getUtilisateurDemandeur(),
                $entity->getMarque(),
                $entity->getCasier()
            ];
        }

        return $data;
    }

    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }

    private function data($request, $ditListeModel, $ditSearch, $option, $em)
    {
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        //recupération des données filtrée
        $paginationData = $em->getRepository(DemandeIntervention::class)->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);

        //ajout de donner du statut achat piece dans data
        $this->ajoutStatutAchatPiece($paginationData['data']);

        //ajout de donner du statut achat locaux dans data
        $this->ajoutStatutAchatLocaux($paginationData['data']);

        //ajout nombre de pièce joint
        $this->ajoutNbrPj($paginationData['data'], $em);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data']);

        $this->ajoutQuatreStatutOr($paginationData['data']);

        $this->ajoutConditionOrEqDit($paginationData['data']);

        $this->ajoutri($paginationData['data'], $ditListeModel, $em);

        $this->ajoutMarqueCasierMateriel($paginationData['data']);
        $this->ajoutEstOrASoumis($paginationData['data'], $em);

        $this->ajoutDateEtMontantOR($paginationData['data'], $em);

        $this->ajoutDateEtMontantOR($paginationData['data'], $em);

        $this->ajoutConditionAnnulationDit($paginationData['data'], $ditListeModel);

        // dd($paginationData['data']);
        return $paginationData;
    }

    private function ajoutConditionAnnulationDit($datas, $ditListeModel)
    {
        foreach ($datas as $data) {
            $estAnnulable = $this->conditionAnnulationDit($data, $ditListeModel);
            $data->setEstAnnulable($estAnnulable);
        }
    }

    private function conditionAnnulationDit($data, $ditListeModel): bool
    {
        $estAnnulable = false; //cacher le boutton Annuler

        //si le statut dit est A_AFFECTER
        $condition1 = $data->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_A_AFFECTER;
        //si le statut dit est AFFECTER_SECTION et l'utilisateur demandeur est l'utilisateur connecté ou profil de l'utilisateur connecté est CHEF_ATELIER
        $condition2 = $data->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_AFFECTEE_SECTION && (strtolower($data->getUtilisateurDemandeur()) === strtolower($this->getUser()->getNomUtilisateur()) || in_array(User::PROFIL_CHEF_ATELIER, $this->getUser()->getRoleIds()));
        //si le statut dit est CLOTUREE_VALIDER et il n'y a pas de numero OR soumi
        $condition3 = $data->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_CLOTUREE_VALIDER && $ditListeModel->getNbNumor($data->getNumeroDemandeIntervention()) == 0;

        if ($condition1 || $condition2 || $condition3) {
            $estAnnulable =  true; //affichage du boutton Annuler
        }

        return $estAnnulable;
    }

    private function ajoutDateEtMontantOR($datas, $em)
    {
        foreach ($datas as $data) {
            if (!empty($data->getNumeroOR()) && $data->getNumeroOR() !== 'NULL') {
                $dateEtMontant = $em->getRepository(DitOrsSoumisAValidation::class)->getDateEtMontantOR($data->getNumeroOR());
                if (!empty($dateEtMontant)) {
                    $data
                        ->setDateSoumissionOR($dateEtMontant[0]['dateSoumission'])
                        ->setMontantTotalOR($dateEtMontant[0]['totalMontant'])
                    ;
                }
            }
        }
    }

    private function ajoutEstOrASoumis($paginationData, $em)
    {
        foreach ($paginationData as $value) {
            // dump($value->getNumeroDemandeIntervention());
            // dump($value->getInternetExterne());
            // dump($value->getIdStatutDemande());
            // dump($value->getInternetExterne() == 'EXTERNE' && $value->getIdStatutDemande()->getId() === 53);

            $statutAffecterSection = $value->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_AFFECTEE_SECTION; //AFFECTER_SECTION
            $statutCloturerValider = $value->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_CLOTUREE_VALIDER; //CLOTUREE_VALIDER
            $statutTerminer = $value->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_TERMINER; //TERMINER
            $estOrSoumis = $em->getRepository(DitOrsSoumisAValidation::class)->existsNumOrEtDit($value->getNumeroOR(), $value->getNumeroDemandeIntervention());

            if ($statutAffecterSection && !$estOrSoumis) { //si la statut DIT est AFFACTER SECTION et il n'y a pas encore d'OR déjà soumi (c'est la première soumission)
                $value->setEstOrASoumi(true); //affichage du boutton Soumission document à valider
            } elseif ($value->getInternetExterne() == 'EXTERNE' && $value->getIdStatutDemande()->getId() === 53) { // 
                $value->setEstOrASoumi(true);
            } elseif ($statutCloturerValider && !$estOrSoumis) {
                $value->setEstOrASoumi(false); //cacher le boutton Soumission document à valider
            } elseif ($statutCloturerValider && $estOrSoumis) {
                $value->setEstOrASoumi(true);
            }
            // elseif ($value->getIdStatutDemande()->getId() === 57 && explode("-", $value->getAgenceServiceDebiteur())[1] === 'LST') {
            //     $value->setEstOrASoumi(true);
            // } 
            elseif ($statutTerminer) { // affichage du bouton Soumission document à valider si le statut dit "TERMINER"
                $value->setEstOrASoumi(true);
            } else {
                $value->setEstOrASoumi(false);
            }
        }
        // die();
    }
}
