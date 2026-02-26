<?php

namespace App\Controller\Traits\da;

use App\Model\dit\DitModel;
use App\Entity\dit\DitSearch;
use App\Entity\admin\utilisateur\Role;
use Symfony\Component\HttpFoundation\Request;

trait DaListeDitTrait
{

    /**
     * Methode pour autorisation de l'admin
     *
     * @return boolean
     */
    private function autorisationRole(): bool
    {
        return $this->hasRoles(Role::ROLE_ADMINISTRATEUR, Role::ROLE_ATELIER);
    }

    /**
     * Methode pour autorise le role energie
     *
     * @return boolean
     */
    private function autorisationRoleEnergie(): bool
    {
        return $this->hasRoles(Role::ROLE_ENERGIE);
    }


    /**
     * Methode pour l'initialisation des donners dans les champs de formulaire
     */
    private function initialisationRechercheDit(): DitSearch
    {

        $criteria = $this->getSessionService()->get('list_dit_da_search_criteria');
        if (!empty($criteria)) {
            $typeDocument = $criteria['typeDocument'] === null ? null : $this->worTypeDocumentRepository->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $this->worNiveauUrgenceRepository->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $this->statutDemandeRepository->find($criteria['statut']->getId());
            $categorie = $criteria['categorie'] === null ? null : $this->categorieAteAppRepository->find($criteria['categorie']);
        } else {
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $categorie = null;
        }

        $this->ditSearch
            ->setStatut($statut)
            ->setNiveauUrgence($niveauUrgence)
            ->setTypeDocument($typeDocument)
            ->setInternetExterne('INTERNE')
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setIdMateriel($criteria['idMateriel'] ?? null)
            ->setNumParc($criteria['numParc'] ?? null)
            ->setNumSerie($criteria['numSerie'] ?? null)
            ->setAgenceEmetteur($criteria['agenceEmetteur'] ?? null)
            ->setServiceEmetteur($criteria['serviceEmetteur'] ?? null)
            ->setAgenceDebiteur($criteria['agenceDebiteur'] ?? null)
            ->setServiceDebiteur($criteria['serviceDebiteur'] ?? null)
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

        return $this->ditSearch;
    }


    /**
     * Ajouter les information de la recherche dans la session
     *
     * @param array $criteria
     * @return void
     */
    private function ajoutCriteredansSession(array $criteriaTab)
    {
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->getSessionService()->set('list_dit_da_search_criteria', $criteriaTab);
    }

    private function recupDataFormulaireRecherhce($form, Request $request): DitSearch
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->ditSearch = $form->getData();
        }
        return $this->ditSearch;
    }

    /**
     * Methode pour recupérer l'agence et service de l'utilisateur connecter
     *
     * @param array $agenceServiceIps
     * @param boolean $autoriser
     * @return array
     */
    private function agenceServiceEmetteur(array $agenceServiceIps, bool $autoriser): array
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

    private function Option(bool $autoriser, bool $autorisationRoleEnergie, array $agenceServiceEmetteur, array $agenceIds, array $serviceIds): array
    {
        return  [
            'boolean' => $autoriser,
            'autorisationRoleEnergie' => $autorisationRoleEnergie,
            'codeAgence' => $agenceServiceEmetteur['agence'] === null ? null : $agenceServiceEmetteur['agence']->getId(),
            'agenceAutoriserIds' => $agenceIds,
            'serviceAutoriserIds' => $serviceIds
        ];
    }


    /**
     * Methode pour recupérer tous les données à afficher
     *
     * @return void
     */
    private function data(Request $request, DitSearch $ditSearch, array $agenceServiceAutorises): array
    {
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        //recupération des données filtrée
        $paginationData = $this->criteriaIsObjectEmpty($ditSearch) ? [] : $this->ditRepository->findPaginatedAndFilteredDa($page, $limit, $ditSearch, $agenceServiceAutorises);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data'] ?? []);

        return $paginationData;
    }

    /** 
     * Méthode pour vérifier si l'objet est vide
     * 
     * @return bool
     */
    private function criteriaIsObjectEmpty(DitSearch $ditSearch): bool
    {
        return
            $ditSearch->getNiveauUrgence() === null &&
            $ditSearch->getStatut() === null &&
            $ditSearch->getIdMateriel() === null &&
            $ditSearch->getTypeDocument() === null &&
            $ditSearch->getInternetExterne() === "INTERNE" &&
            $ditSearch->getDateDebut() === null &&
            $ditSearch->getDateFin() === null &&
            $ditSearch->getDateFin() === null &&
            $ditSearch->getNumParc() === null &&
            $ditSearch->getNumParc() === null &&
            $ditSearch->getNumSerie() === null &&
            $ditSearch->getAgenceEmetteur() === null &&
            $ditSearch->getServiceEmetteur() === null &&
            $ditSearch->getAgenceDebiteur() === null &&
            $ditSearch->getServiceDebiteur() === null &&
            $ditSearch->getNumDit() === null &&
            $ditSearch->getNumOr() === null &&
            $ditSearch->getStatutOr() === null &&
            $ditSearch->getDitSansOr() === null &&
            $ditSearch->getCategorie() === null &&
            $ditSearch->getUtilisateur() === null &&
            $ditSearch->getSectionAffectee() === null &&
            $ditSearch->getSectionSupport1() === null &&
            $ditSearch->getSectionSupport2() === null &&
            $ditSearch->getSectionSupport3() === null &&
            $ditSearch->getEtatFacture() === null &&
            $ditSearch->getNumDevis() === "";
    }

    /**
     * Methode qui recupère le n° serie et n° parc de chaque dit et l'ajouter dans les données à afficher
     *
     * @param array $data
     * @return void
     */
    private function ajoutNumSerieNumParc(array $data)
    {
        $ditModel = new DitModel();
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                if (!empty($data[$i]->getIdMateriel())) {

                    // Associez chaque entité à ses valeurs de num_serie et num_parc
                    $numSerieParc = $ditModel->recupNumSerieParc($data[$i]->getIdMateriel());
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
}
