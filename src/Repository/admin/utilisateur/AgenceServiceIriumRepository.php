<?php

namespace App\Repository\admin\utilisateur;

use Doctrine\ORM\EntityRepository;

class AgenceServiceIriumRepository extends EntityRepository
{
    // Ajoutez des méthodes personnalisées ici
    public function findId($agenceIps, $serviceIps)
    {
        $queryBuilder = $this->createQueryBuilder('asi')
        ->select('asi.id')
        ->where('asi.agence_ips IN (:agenceIps)')
        ->setParameter('agenceIps', $agenceIps)
        ->andWhere('asi.service_ips IN (:serviceIps)')
        ->setParameter('serviceIps', $serviceIps);

        $query = $queryBuilder->getQuery();
        $result = $query->getScalarResult(); // Utilisation de getScalarResult pour un tableau simple

        // Extraction des ids dans un tableau
        $ids = array_column($result, 'id');

        return $ids;
    }
}