<?php

namespace App\Model\ddd;

use App\Model\Model;
use Doctrine\ORM\EntityManagerInterface;

class DemandeDiagnosticPneuModel extends Model
{

    /**
     * informix
     */
    public function findAllValaibleMateriel($matricule = '0',  $numParc = '0', $numSerie = '0')
    {
        if ($matricule === '' || $matricule === '0' || $matricule === null) {
            $conditionNummat = "";
        } else {
            $conditionNummat = "and mmat_nummat = '" . $matricule . "'";
        }


        if ($numParc === '' || $numParc === '0' || $numParc === null) {
            $conditionNumParc = "";
        } else {
            $conditionNumParc = "and mmat_recalph = '" . $numParc . "'";
        }

        if ($numSerie === '' || $numSerie === '0' || $numSerie === null) {
            $conditionNumSerie = "";
        } else {
            $conditionNumSerie = "and TRIM(mmat_numserie) = '" . $numSerie . "'";
        }




        $statement = "SELECT
       
        mmat_marqmat as constructeur,
        trim(mmat_desi) as designation,
        trim(mmat_typmat) as modele,
        trim(mmat_numparc) as casier_emetteur,
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc,

        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as heure,
        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as km,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Prix_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 10 and mofi_ssclasse in (100,21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChiffreAffaires,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (100,110) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeLocative,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien
      
      FROM MAT_MAT
      LEFT JOIN mat_bil on mbil_nummat = mmat_nummat and mbil_dateclot <= '01/01/1900' and mbil_dateclot = '12/31/1899'
      WHERE MMAT_ETSTOCK in ('ST','AT', '--')
      AND MMAT_AFFECT <> 'CAS'
      " . $conditionNummat . "
      " . $conditionNumParc . "
      " . $conditionNumSerie . "
      ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public ?int $id = null;

    public ?string $numeroDemande = null;

    public ?int $idChantier = null;
    public ?string $codeChantier = null;
    public ?string $nomChantier = null;

    public ?int $idMateriel = null;
    public ?string $numeroParcMateriel = null;
    public ?string $marqueMateriel = null;
    public ?string $typeMateriel = null;
    public ?string $designationMateriel = null;

    public ?string $dateDepartChantier = null;

    public ?string $livraison = null;

    public ?int $nbPneuSurMachine = null;
    public ?int $nbPneuSecours = null;
    public ?int $nbPneuADiagnostiquer = null;

    public ?string $observation = null;

    /**
     * @var string[]
     */
    public array $motifs = [];

    public ?string $demandeur = null;

    public ?string $dateCreation = null;

    public ?string $statut = null;

    public ?string $numeroDit = null;

    public ?string $numeroOr = null;

    /**
     * Génère un numéro de demande au format DDD + AA + MM + compteur 4 chiffres
     * Exemple : DDD25030001 pour mars 2025, première demande du mois.
     *
     * @param EntityManagerInterface $em
     * @return string
     * @throws \Exception
     */
    public function genererNumeroDemande(EntityManagerInterface $em): string
    {
        $now = new \DateTime();
        $annee = $now->format('y'); // 2 derniers chiffres
        $mois  = $now->format('m');

        $prefix = 'DDD' . $annee . $mois;

        // Récupérer le dernier numéro pour ce préfixe (ex: DDD2503...)
        $qb = $em->createQueryBuilder();
        $qb->select('ddp.numeroDemande')
            ->from('App\Entity\ddd\DemandeDiagnosticPneu', 'ddp')
            ->where($qb->expr()->like('ddp.numeroDemande', ':prefix'))
            ->setParameter('prefix', $prefix . '%')
            ->orderBy('ddp.numeroDemande', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            $dernierNumero = $result['numeroDemande'];
            $compteur = (int) substr($dernierNumero, -4);
            $nouveauCompteur = str_pad($compteur + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nouveauCompteur = '0001';
        }

        return $prefix . $nouveauCompteur;
    }
}
