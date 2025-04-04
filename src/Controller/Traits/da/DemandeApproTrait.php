<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitSearch;
use Symfony\Component\HttpFoundation\Request;

trait DemandeApproTrait
{


    /**
     * Methode pour recupérer tous les données à afficher
     *
     * @param Request $request
     * @param array $option
     * @return void
     */
    private function data(Request $request, array $option, DitSearch $criteria): array
    {
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        //recupération des données filtrée
        $paginationData = $this->ditRepository->findPaginatedAndFilteredDa($page, $limit, $criteria, $option);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data']);

        return $paginationData;
    }

    /**
     * Methode qui recupère le n° serie et n° parc de chaque dit et l'ajouter dans les données à afficher
     *
     * @param array $data
     * @return void
     */
    private function ajoutNumSerieNumParc(array $data)
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

    /**
     * Ajouter les information de la recherche dans la session
     *
     * @param array $criteria
     * @return void
     */
    private function ajoutCriteredansSession(array $criteriaTab)
    {
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('dit_search_criteria', $criteriaTab);
    }

    /**
     * Methode pour autorisation de l'admin
     *
     * @return boolean
     */
    private function autorisationRole(): bool
    {
        $userConnecter = $this->getUser();
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds);
    }

    /**
     * Methode pour autorise le role atelier
     *
     * @return boolean
     */
    private function autorisationRoleEnergie(): bool
    {
        $userConnecter = $this->getUser();
        $roleIds = $userConnecter->getRoleIds();
        return in_array(5, $roleIds);
    }

    /**
     * Methode pour l'initialisation des donners dans les champs de formulaire
     */
    private function initialisationRechercheDit(): DitSearch
    {

        $criteria = $this->sessionService->get('dit_search_criteria', []);
        if ($criteria !== null) {
            $agenceIpsEmetteur = null;
            $serviceIpsEmetteur = null;
            $typeDocument = $criteria['typeDocument'] === null ? null : $this->worTypeDocumentRepository->find($criteria['typeDocument']->getId());
            $niveauUrgence = $criteria['niveauUrgence'] === null ? null : $this->worNiveauUrgenceRepository->find($criteria['niveauUrgence']->getId());
            $statut = $criteria['statut'] === null ? null : $this->statutDemandeRepository->find($criteria['statut']->getId());
            $serviceEmetteur = $criteria['serviceEmetteur'] === null ? $serviceIpsEmetteur : $this->serviceRepository->find($criteria['serviceEmetteur']->getId());
            $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $this->serviceRepository->find($criteria['serviceDebiteur']->getId());
            $agenceEmetteur = $criteria['agenceEmetteur'] === null ? $agenceIpsEmetteur : $this->agenceRepository->find($criteria['agenceEmetteur']->getId());
            $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $this->agenceRepository->find($criteria['agenceDebiteur']->getId());
            $categorie = $criteria['categorie'] === null ? null : $this->categorieAteAppRepository->find($criteria['categorie']);
        } else {
            $agenceIpsEmetteur = null;
            $serviceIpsEmetteur = null;
            $typeDocument = null;
            $niveauUrgence = null;
            $statut = null;
            $agenceEmetteur = $agenceIpsEmetteur;
            $serviceEmetteur = $serviceIpsEmetteur;
            $serviceDebiteur = null;
            $agenceDebiteur = null;
            $categorie = null;
        }

        $this->ditSearch
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

        return $this->ditSearch;
    }

    private function initialisationDemandeAppro(DemandeAppro $demandeAppro, DemandeIntervention $dit)
    {
        $demandeAppro
            ->setDit($dit)
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal('Ouvert')
        ;
    }
}
