<?php

namespace App\Repository\contrat;

use App\Entity\contrat\Contrat;
use Doctrine\ORM\EntityRepository;

/**
 * Repository pour l'entité Contrat
 */
class ContratRepository extends EntityRepository
{
    /**
     * Recherche paginée des contrats avec filtres
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre de résultats par page
     * @param Contrat $contratSearch Objet de recherche
     * @param array $options Options supplémentaires
     * @return array Données paginées
     */
    public function findPaginatedAndFiltered(int $page, int $limit, Contrat $contratSearch, array $options = []): array
    {
        $qb = $this->createQueryBuilder('c');

        // Appliquer les filtres
        $this->applyFilters($qb, $contratSearch, $options);

        // Trier par id décroissante
        $qb->orderBy('c.id', 'DESC');

        // Pagination
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        $results = $query->getResult();

        // Compter le total
        $countQb = $this->createQueryBuilder('c');
        $countQb->select('COUNT(c.id)');
        $this->applyFilters($countQb, $contratSearch, $options);
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data' => $results,
            'currentPage' => $page,
            'totalItems' => $total,
            'itemsPerPage' => $limit,
            'totalPages' => ceil($total / $limit),
            'lastPage' => ceil($total / $limit)
        ];
    }

    /**
     * Appliquer les filtres de recherche
     */
    private function applyFilters($qb, Contrat $contratSearch, array $options = []): void
    {
        $where = $qb->expr()->andX();

        // Filtre par référence (gestion des références multiples)
        if (isset($options['references']) && is_array($options['references']) && !empty($options['references'])) {
            // Recherche multiple : OR sur toutes les références
            $orX = $qb->expr()->orX();
            foreach ($options['references'] as $reference) {
                $orX->add($qb->expr()->like('c.reference', ':reference_' . md5($reference)));
                $qb->setParameter('reference_' . md5($reference), '%' . $reference . '%');
            }
            $where->add($orX);
        } elseif ($contratSearch->getReferenceSearch()) {
            // Recherche simple
            $where->add($qb->expr()->like('c.reference', ':reference'));
            $qb->setParameter('reference', '%' . $contratSearch->getReferenceSearch() . '%');
        }

        // Filtre par date d'enregistrement (début)
        if ($contratSearch->getDateEnregistrementDebut()) {
            $where->add($qb->expr()->gte('c.date_enregistrement', ':date_enregistrement_debut'));
            $qb->setParameter('date_enregistrement_debut', $contratSearch->getDateEnregistrementDebut());
        }

        // Filtre par date d'enregistrement (fin)
        if ($contratSearch->getDateEnregistrementFin()) {
            $where->add($qb->expr()->lte('c.date_enregistrement', ':date_enregistrement_fin'));
            $qb->setParameter('date_enregistrement_fin', $contratSearch->getDateEnregistrementFin());
        }

        // Filtre par agence (champ texte)
        if ($contratSearch->getAgenceSearch()) {
            $where->add($qb->expr()->eq('c.agence', ':agence'));
            $qb->setParameter('agence', $contratSearch->getAgenceSearch());
        }

        // Filtre par service (champ texte)
        if ($contratSearch->getServiceSearch()) {
            $where->add($qb->expr()->eq('c.service', ':service'));
            $qb->setParameter('service', $contratSearch->getServiceSearch());
        }

        // Filtre par partenaire
        if ($contratSearch->getNomPartenaireSearch()) {
            $where->add($qb->expr()->like('c.nom_partenaire', ':nom_partenaire'));
            $qb->setParameter('nom_partenaire', '%' . $contratSearch->getNomPartenaireSearch() . '%');
        }

        // Filtre par type de tiers
        if ($contratSearch->getTypeTiersSearch()) {
            $where->add($qb->expr()->eq('c.type_tiers', ':type_tiers'));
            $qb->setParameter('type_tiers', $contratSearch->getTypeTiersSearch());
        }

        // Filtre par date de début de contrat
        if ($contratSearch->getDateDebutContrat()) {
            $where->add($qb->expr()->gte('c.date_debut_contrat', ':date_debut_contrat'));
            $qb->setParameter('date_debut_contrat', $contratSearch->getDateDebutContrat());
        }

        // Filtre par date de fin de contrat
        if ($contratSearch->getDateFinContrat()) {
            $where->add($qb->expr()->lte('c.date_fin_contrat', ':date_fin_contrat'));
            $qb->setParameter('date_fin_contrat', $contratSearch->getDateFinContrat());
        }

        // Filtre par statut
        if ($contratSearch->getStatut()) {
            $where->add($qb->expr()->eq('c.statut', ':statut'));
            $qb->setParameter('statut', $contratSearch->getStatut());
        }

        // Appliquer filtre sur agence et service si non admin
        if (!$options['admin']) {
            // Filtre par agence autoriser
            if (isset($options['agenceAutoriser']) && !empty($options['agenceAutoriser'])) {
                $where->add($qb->expr()->in('c.agence', ':agenceAutoriser'));
                $qb->setParameter('agenceAutoriser', $options['agenceAutoriser']);
            }

            // Filtre par service autoriser
            if (isset($options['serviceAutoriser']) && !empty($options['serviceAutoriser'])) {
                $where->add($qb->expr()->in('c.service', ':serviceAutoriser'));
                $qb->setParameter('serviceAutoriser', $options['serviceAutoriser']);
            }
        }

        // Vérifier si $where a des éléments avant de l'ajouter
        if ($where->count() > 0) {
            $qb->andWhere($where);
        }
    }

    /**
     * Trouver tous les contrats pour export Excel
     */
    public function findAllForExport(Contrat $contratSearch, array $options = []): array
    {
        $qb = $this->createQueryBuilder('c');
        $this->applyFilters($qb, $contratSearch, $options);
        $qb->orderBy('c.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouver un contrat par son ID
     */
    public function findWithDetails(int $id): ?Contrat
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
