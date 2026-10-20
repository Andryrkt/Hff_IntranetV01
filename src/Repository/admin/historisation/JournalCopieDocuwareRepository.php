<?php

namespace App\Repository\admin\historisation;

use Doctrine\ORM\EntityRepository;

class JournalCopieDocuwareRepository extends EntityRepository
{
    /**
     * Recherche dans le journal, les plus récentes en premier
     *
     * @param string|null $recherche     texte recherché dans le nom du fichier (n° OR, DIT, DA...) ou l'utilisateur
     * @param bool        $echecSeulement ne retourner que les copies en échec
     */
    public function rechercher(?string $recherche, bool $echecSeulement, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, int $limit = 300): array
    {
        $qb = $this->createQueryBuilder('j')
            ->orderBy('j.dateCopie', 'DESC')
            ->setMaxResults($limit);

        if ($recherche !== null && trim($recherche) !== '') {
            $qb->andWhere('j.nomFichier LIKE :recherche OR j.fichierSource LIKE :recherche OR j.utilisateur LIKE :recherche')
                ->setParameter('recherche', '%' . trim($recherche) . '%');
        }
        if ($echecSeulement) {
            $qb->andWhere('j.succes = 0');
        }
        if ($dateDebut) {
            $qb->andWhere('j.dateCopie >= :dateDebut')->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('j.dateCopie <= :dateFin')->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    public function countEchecsNonVus(): int
    {
        return (int) $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.succes = 0')
            ->andWhere('j.estVu = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function marquerEchecsVus(): void
    {
        $this->createQueryBuilder('j')
            ->update()
            ->set('j.estVu', 1)
            ->where('j.succes = 0')
            ->andWhere('j.estVu = 0')
            ->getQuery()
            ->execute();
    }
}
