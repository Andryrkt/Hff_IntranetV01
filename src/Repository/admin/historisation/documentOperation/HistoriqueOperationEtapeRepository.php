<?php

namespace App\Repository\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use Doctrine\ORM\EntityRepository;

class HistoriqueOperationEtapeRepository extends EntityRepository
{
    /**
     * Recherche les opérations suivies, les plus récentes en premier, avec leurs étapes dans l'ordre.
     *
     * @param string|null $recherche       n° de document ou utilisateur
     * @param string|null $typeOperation  une des constantes SuiviOperationService::TYPE_*
     * @param bool        $problemeSeulement ne garder que les opérations avec une étape en échec, interrompue ou jamais terminée
     * @param int|null    $idHistorique    opération rattachée à cette ligne de l'historique des opérations
     * @param bool        $nonLuSeulement  ne garder que les opérations en problème pas encore marquées comme lues
     *
     * @return array<int, array{idSuivi: string, statut: string, etapes: HistoriqueOperationEtape[]}>
     */
    public function rechercherSuivis(?string $recherche, ?string $typeOperation, bool $problemeSeulement, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, ?int $idHistorique = null, bool $nonLuSeulement = false, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.idSuivi, MIN(e.dateDebut) AS HIDDEN debut')
            ->groupBy('e.idSuivi')
            ->orderBy('debut', 'DESC')
            ->setMaxResults($limit);

        if ($recherche !== null && trim($recherche) !== '') {
            $qb->andWhere('e.numeroDocument LIKE :recherche OR e.utilisateur LIKE :recherche')
                ->setParameter('recherche', '%' . trim($recherche) . '%');
        }
        if ($typeOperation) {
            $qb->andWhere('e.typeOperation = :type')->setParameter('type', $typeOperation);
        }
        if ($idHistorique) {
            $qb->andWhere('e.idHistoriqueOperation = :idHistorique')->setParameter('idHistorique', $idHistorique);
        }
        if ($dateDebut) {
            $qb->andWhere('e.dateDebut >= :dateDebut')->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('e.dateDebut <= :dateFin')->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'));
        }
        // une opération non lue n'a de sens que si elle est en problème
        if ($problemeSeulement || $nonLuSeulement) {
            $qb->having('SUM(CASE WHEN e.statut <> :succes THEN 1 ELSE 0 END) > 0')
                ->setParameter('succes', SuiviOperationService::STATUT_SUCCES);
        }
        if ($nonLuSeulement) {
            $qb->andWhere('e.estLu = :nonLu')->setParameter('nonLu', false);
        }

        $idsSuivi = array_column($qb->getQuery()->getScalarResult(), 'idSuivi');
        if (empty($idsSuivi)) {
            return [];
        }

        /** @var HistoriqueOperationEtape[] $etapes */
        $etapes = $this->createQueryBuilder('e')
            ->where('e.idSuivi IN (:ids)')
            ->setParameter('ids', $idsSuivi)
            ->orderBy('e.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        // regroupement par opération, dans l'ordre de la recherche
        $suivis = array_fill_keys($idsSuivi, []);
        foreach ($etapes as $etape) {
            $suivis[$etape->getIdSuivi()][] = $etape;
        }

        $resultat = [];
        foreach ($suivis as $idSuivi => $etapesSuivi) {
            $resultat[] = [
                'idSuivi' => $idSuivi,
                'statut'  => $this->statutGlobal($etapesSuivi),
                'etapes'  => $etapesSuivi,
            ];
        }

        return $resultat;
    }

    /**
     * Marque toutes les étapes d'une opération comme lues (ou non lues)
     *
     * @return int nombre d'étapes mises à jour
     */
    public function marquerLu(string $idSuivi, bool $lu, ?string $utilisateur): int
    {
        return $this->createQueryBuilder('e')
            ->update()
            ->set('e.estLu', ':lu')
            ->set('e.luPar', ':luPar')
            ->set('e.dateLecture', ':dateLecture')
            ->where('e.idSuivi = :idSuivi')
            ->setParameter('lu', $lu)
            ->setParameter('luPar', $lu ? $utilisateur : null)
            ->setParameter('dateLecture', $lu ? new \DateTime() : null)
            ->setParameter('idSuivi', $idSuivi)
            ->getQuery()
            ->execute();
    }

    /**
     * ECHEC si une étape a échoué, INTERROMPU si une étape ne s'est pas terminée,
     * ALERTE si un contrôle signale une anomalie, sinon SUCCES
     *
     * @param HistoriqueOperationEtape[] $etapes
     */
    private function statutGlobal(array $etapes): string
    {
        $statuts = array_map(function (HistoriqueOperationEtape $etape) {
            return $etape->getStatut();
        }, $etapes);

        if (in_array(SuiviOperationService::STATUT_ECHEC, $statuts, true)) {
            return SuiviOperationService::STATUT_ECHEC;
        }
        if (in_array(SuiviOperationService::STATUT_INTERROMPU, $statuts, true) || in_array(SuiviOperationService::STATUT_EN_COURS, $statuts, true)) {
            return SuiviOperationService::STATUT_INTERROMPU;
        }
        if (in_array(SuiviOperationService::STATUT_ALERTE, $statuts, true)) {
            return SuiviOperationService::STATUT_ALERTE;
        }

        return SuiviOperationService::STATUT_SUCCES;
    }
}
