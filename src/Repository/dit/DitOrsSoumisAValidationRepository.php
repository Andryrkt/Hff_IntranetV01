<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitOrsSoumisAValidationRepository extends EntityRepository
{

    public function existsNumOrEtDit(?string $numOr, string $numDit): bool
    {
        $qb = $this->createQueryBuilder('osv');
        $qb->select('1')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->setParameter('numOr', $numOr)
            ->setMaxResults(1);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function findNumOrItvValide()
    {
        $query = $this->createQueryBuilder('osv')
            ->select("DISTINCT CONCAT(osv.numeroOR, '-', osv.numeroItv) AS numeroORNumeroItv")
            ->where('osv.statut IN (:statut)')
            ->setParameter('statut', ['Validé', 'Livré', 'Livré partiellement'])
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }

    public function findNumOrValide()
    {
        $query = $this->createQueryBuilder('osv')
            ->select("DISTINCT osv.numeroOR AS numeroOR")
            ->where('osv.statut IN (:statut)')
            ->setParameter('statut', ['Validé', 'Livré', 'Livré partiellement'])
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }

    public function findNbrItv($numOr)
    {
        $nbrItv = $this->createQueryBuilder('osv')
            ->select('COUNT(osv.numeroItv)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        return $nbrItv ? $nbrItv : 0;
    }

    public function findNumItvValide($numOr): array
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        $statut = ['Validé', 'Livré', 'Livré partiellement'];

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le numero d'intervention
        $nbrItv = $this->createQueryBuilder('osv')
            ->select('osv.numeroItv')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.statut IN (:statut)')
            ->andwhere('osv.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'statut' => $statut,
            ])
            ->getQuery()
            ->getSingleColumnResult();

        return $nbrItv;
    }


    public function findStatutByNumeroVersionMax($numOr, $numItv)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $statut = $this->createQueryBuilder('osv')
            ->select('osv.statut')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroItv = :numItv')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'numItv' => $numItv,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $statut;
    }


    public function findNumeroVersionMax($numOr)
    {
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    public function findOrSoumiAvant($numOr)
    {
        $qb = $this->createQueryBuilder('osv');

        $subquery = $this->createQueryBuilder('osv2')
            ->select('MAX(osv2.numeroVersion)')
            ->where('osv2.numeroOR = :numOr')
            ->getDQL();

        $orSoumisAvant = $qb
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->andWhere($qb->expr()->eq('osv.numeroVersion', '(' . $subquery . ')'))
            ->getQuery()
            ->getResult();

        return $orSoumisAvant;
    }

    public function findOrSoumiAvantMax($numOr)
    {
        // Étape 1: Récupérer la version maximale pour le numeroOR donné
        $qbMax = $this->createQueryBuilder('osv2')
            ->select('MAX(osv2.numeroVersion)')
            ->where('osv2.numeroOR = :numOr')
            ->setParameter('numOr', $numOr);

        $maxVersion = $qbMax->getQuery()->getSingleScalarResult();

        if ($maxVersion === null || $maxVersion == 1) {
            // Si la version max est 1 ou nulle, il n'y a pas de version avant la version maximale
            return null;
        }

        // Étape 2: Récupérer la ligne qui a la version juste avant la version max
        $qb = $this->createQueryBuilder('osv')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroVersion = :previousVersion')
            ->setParameter('numOr', $numOr)
            ->setParameter('previousVersion', $maxVersion - 1)  // Juste avant la version max
            ->getQuery()
            ->getResult();

        return $qb;
    }


    public function findMontantValide($numOr, $numItv)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Vérifier si un numeroVersion a été trouvé
        if ($numeroVersionMax === null) {
            return [
                "statut" => "echec",
                "message" => "Aucune version trouvée pour le numeroOR {$numOr}."
            ];
        }
        // dd($numOr, $numItv, (int)$numeroVersionMax);

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le montant valide
        $montantValide = $this->createQueryBuilder('osv')
            ->select('osv.montantItv')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroItv = :numItv')
            ->setParameters([
                'numeroVersionMax' => (int)$numeroVersionMax,
                'numOr' => $numOr,
                'numItv' => $numItv,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        // Vérifier si un montant a été trouvé
        if ($montantValide === null) {
            return [
                "statut" => "echec",
                "message" => "Aucun montant valide trouvé pour le numeroOR {$numOr} et le numeroItv {$numItv}."
            ];
        }

        return $montantValide;
    }


    public function findOrSoumisValid($numOr)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $montantValide = $this->createQueryBuilder('osv')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->andWhere('osv.statut IN (:statut)')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'statut' => ['Validé', 'Livré', 'Livré partiellement'],
            ])
            ->getQuery()
            ->getResult();

        return $montantValide;
    }

    /**
     * recupère tous les numéros OR Distincts
     *
     * @return void
     */
    public function findNumOrAll()
    {
        $query = $this->createQueryBuilder('osv')
            ->select("DISTINCT osv.numeroOR")
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }

    /**
     * Recupère tous les numéros ITV Distincts
     *
     * @return void
     */
    public function findNumOrItvAll()
    {
        $query = $this->createQueryBuilder('osv')
            ->select("DISTINCT CONCAT(osv.numeroOR, '-', osv.numeroItv) AS numeroORNumeroItv")
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }

    /**
     * cette méthode permet de vérifier si un OR doit être bloqué ou non
     * tous les statuts qui contiennent "Validé", "Refusé", "Livré partiellement", "Modification demandée par client", "Modification demandée par CA" ne sont pas bloqués
     *
     * @param string $numOr
     * @return void
     */
    public function getblocageStatut(string $numOr, string $numDit): string
    {
        $qb = $this->createQueryBuilder('o');

        // Étape 1 : Vérifier l'existence
        $count = $qb
            ->select('COUNT(o.id)')
            ->where('o.numeroOR = :numOr')
            ->andWhere('o.numeroDit = :numDit')
            ->setParameters([
                'numOr' => $numOr,
                'numDit' => $numDit
            ])
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $count === 0) {
            return 'ne pas bloquer';
        }

        // Étape 2 : Récupérer la version max
        $maxVersion = $this->createQueryBuilder('o')
            ->select('MAX(o.numeroVersion)')
            ->where('o.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 3 : Vérifier les statuts avec like()
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)')
            ->where('o.numeroOR = :numOr')
            ->andWhere('o.numeroVersion = :maxVersion')
            ->andWhere(
                $expr->orX(
                    $expr->like('o.statut', ':valide'),
                    $expr->like('o.statut', ':refuse'),
                    $expr->like('o.statut', ':livre_part'),
                    $expr->like('o.statut', ':modif_client'),
                    $expr->like('o.statut', ':modif_ca'),
                    $expr->like('o.statut', ':modif_dt')
                )
            )
            ->setParameters([
                'numOr' => $numOr,
                'maxVersion' => $maxVersion,
                'valide' => '%Validé%',
                'refuse' => '%Refusé%',
                'livre_part' => '%Livré partiellement%',
                'modif_client' => '%Modification demandée par client%',
                'modif_ca' => '%Modification demandée par CA%',
                'modif_dt' => '%Modification demandée par DT%',

            ]);

        $matchingCount = $qb->getQuery()->getSingleScalarResult();

        return ((int) $matchingCount > 0) ? 'ne pas bloquer' : 'bloquer';
    }

    public function getDateEtMontantOR($numOr)
    {
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('osv');
        $qb->select('osv.dateSoumission, SUM(osv.montantItv) AS totalMontant')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numOr' => $numOr,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->groupBy('osv.dateSoumission');;

        return $qb->getQuery()->getResult();
    }

    public function getNbrOrSoumis(string $numOr)
    {
        return  $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getStatut(string $numDit)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return null;
        }

        // Étape 2 : Récupérer le statut
        $result = $this->createQueryBuilder('osv')
            ->select('DISTINCT osv.statut')
            ->where('osv.numeroDit = :numDit')
            ->andWhere('osv.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numDit' => $numDit,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->getQuery()
            ->getOneOrNullResult(); // retourne un tableau associatif ou null

        return $result['statut'] ?? null;
    }

    public function findDerniereVersionByNumeroDit(string $numeroDit)
    {
        $qb = $this->createQueryBuilder('osav');

        $subQb = $this->createQueryBuilder('sub')
            ->select('MAX(sub.numeroVersion)')
            ->where('sub.numeroDit = :numeroDit')
            ->getDQL();

        $qb->where('osav.numeroDit = :numeroDit')
            ->andWhere('osav.numeroVersion = (' . $subQb . ')')
            ->setParameter('numeroDit', $numeroDit);

        return $qb->getQuery()->getResult();
    }

    public function getNumeroEtStatutOr(string $numDit): array
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return [null, null];
        }

        // Étape 2 : Récupérer le statut et numero OR
        $result = $this->createQueryBuilder('osv')
            ->select('DISTINCT osv.statut as statutOr, osv.numeroOR as numOr')
            ->where('osv.numeroDit = :numDit')
            ->andWhere('osv.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numDit' => $numDit,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->getQuery()
            ->getOneOrNullResult(); // On prend une seule ligne

        return [$result['numOr'] ?? null, $result['statutOr'] ?? null];
    }
}
