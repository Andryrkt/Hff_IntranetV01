<?php

namespace App\Repository\dw;

use App\Entity\cde\CdefnrSoumisAValidationLigne;
use Doctrine\ORM\EntityRepository;

class DwBcClientNegoceRepository extends EntityRepository
{
    /**
     * Récupère le dernier BCC négocié validé pour un numéro de devis donné.
     *
     * @param string $numeroDevis
     *
     * @return array{numeroBccNeg:string,path:string}|null
     */
    public function findLastValidatedBcc(string $numeroDevis): ?array
    {
        $result = $this->createQueryBuilder('d')
            ->where('d.numeroDevis = :numeroDevis')
            ->andWhere('d.statutBccNeg = :statut')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setParameter('statut', 'Validé')
            ->orderBy('d.idBccNeg', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Si $result est une entité, faites :
        if ($result) {
            return [
                'numeroBccNeg' => $result->getNumeroBccNeg(),
                'path' => $result->getPath()
            ];
        }

        return null;
    }

    /**
     * Récupère les fichiers (path) des BC client négoce rattachés aux commandes
     * fournisseur données (lignes de type "NEG" de cdefnr_soumis_a_validation_ligne).
     *
     * @param string[] $numeroCdes
     *
     * @return array<array{path:string,numeroBccNeg:string,numeroCde:string}>
     */
    public function findPathsByNumeroCdes(array $numeroCdes): array
    {
        if (empty($numeroCdes)) {
            return [];
        }

        return $this->createQueryBuilder('d')
            ->select('DISTINCT d.path, d.numeroBccNeg, l.numCde AS numeroCde')
            ->from(CdefnrSoumisAValidationLigne::class, 'l')
            ->where('l.numeroDocument = d.numeroBccNeg')
            ->andWhere('l.typeDocument = :typeDocument')
            ->andWhere('l.numCde IN (:numeroCdes)')
            ->andWhere('d.path IS NOT NULL')
            ->setParameter('typeDocument', 'NEG')
            ->setParameter('numeroCdes', $numeroCdes)
            ->getQuery()
            ->getResult();
    }
}
