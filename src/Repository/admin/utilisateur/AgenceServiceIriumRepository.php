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

    public function findByAgenceServices(array $agenceServices, string $codeAgence, string $codeService)
    {
        $queryBuilder = $this->createQueryBuilder('asi')
            ->select('asi.id');

        if (!empty($agenceServices)) {

            $ORX = $queryBuilder->expr()->orX();
            foreach ($agenceServices as $i => $tab) {
                $ORX->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('asi.agence_ips', ':agenceIps_' . $i),
                        $queryBuilder->expr()->eq('asi.service_ips', ':serviceIps_' . $i)
                    )
                );
                $queryBuilder->setParameter('agenceIps_' . $i, $tab['agence_code']);
                $queryBuilder->setParameter('serviceIps_' . $i, $tab['service_code']);
            }

            $queryBuilder->andWhere($ORX);
        } else {
            $queryBuilder->andWhere('asi.agence_ips =:agenceCodeUser')
                ->andWhere('asi.service_ips =:serviceCodeUser')
                ->setParameters([
                    'agenceCodeUser' => $codeAgence,
                    'serviceCodeUser' => $codeService
                ]);
        }

        $query = $queryBuilder->getQuery();
        $result = $query->getScalarResult(); // Utilisation de getScalarResult pour un tableau simple

        // Extraction des ids dans un tableau
        $ids = array_column($result, 'id');

        return $ids;
    }
}
