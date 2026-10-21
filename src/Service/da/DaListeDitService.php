<?php

namespace App\Service\da;

use App\Model\dit\DitModel;
use App\Entity\dit\DitSearch;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Données de la liste des DIT affichée dans le module DA.
 */
class DaListeDitService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Récupère les données à afficher (page de 20 DIT, filtrées par la recherche)
     */
    public function data(int $page, DitSearch $ditSearch, int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, string $codeAgenceUser, bool $peutVoirListeAvecDebiteur, string $codeSociete, bool $multisuccursale): array
    {
        //nombre de ligne par page
        $limit = 20;

        //recupération des données filtrée
        $paginationData = $this->criteriaIsObjectEmpty($ditSearch)
            ? []
            : $this->em->getRepository(DemandeIntervention::class)->findPaginatedAndFilteredDa($ditSearch, $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $codeAgenceUser, $peutVoirListeAvecDebiteur, $codeSociete, $multisuccursale, $page, $limit);

        //recuperation de numero de serie et parc pour l'affichage
        $this->ajoutNumSerieNumParc($paginationData['data'] ?? []);

        return $paginationData;
    }

    /**
     * Méthode pour vérifier si l'objet est vide
     */
    public function criteriaIsObjectEmpty(DitSearch $ditSearch): bool
    {
        return
            $ditSearch->getNiveauUrgence() === null &&
            $ditSearch->getStatut() === null &&
            $ditSearch->getIdMateriel() === null &&
            $ditSearch->getTypeDocument() === null &&
            $ditSearch->getInternetExterne() === "INTERNE" &&
            $ditSearch->getDateDebut() === null &&
            $ditSearch->getDateFin() === null &&
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
     * Recupère le n° serie et n° parc de chaque dit et les ajoute dans les données à afficher
     */
    private function ajoutNumSerieNumParc(array $data): void
    {
        $ditModel = new DitModel();
        foreach ($data as $dit) {
            if (empty($dit->getIdMateriel())) {
                continue;
            }

            $numSerieParc = $ditModel->recupNumSerieParc($dit->getIdMateriel());
            if (!empty($numSerieParc)) {
                $dit->setNumSerie($numSerieParc[0]['num_serie']);
                $dit->setNumParc($numSerieParc[0]['num_parc']);
            } else {
                $dit->setNumSerie('');
                $dit->setNumParc('');
            }
        }
    }
}
