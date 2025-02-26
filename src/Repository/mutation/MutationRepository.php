<?php

namespace App\Repository\mutation;

use Doctrine\ORM\EntityRepository;

class MutationRepository extends EntityRepository
{
    public function findLastNumtel($matricule)
    {
        try {
            $numTel = $this->createQueryBuilder('m')
                ->select('m.numeroTel')
                ->where('m.matricule = :matricule')
                ->setParameter('matricule', $matricule)
                ->orderBy('m.dateDemande', 'DESC') // Tri décroissant par date ou un autre critère pertinent
                ->setMaxResults(1) // Récupérer seulement le dernier numéro
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            // Si aucun résultat n'est trouvé, retourner null ou une valeur par défaut
            return null;
        }

        return $numTel;
    }
}
