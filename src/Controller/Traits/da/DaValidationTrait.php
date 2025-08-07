<?php

namespace App\Controller\Traits\da;

use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;

trait DaValidationTrait
{
    use DaTrait;
    use EntityManagerAwareTrait;

    /** 
     * Modification des tables DemandeAppro, DemandeApproL et DemandeApproLR
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @param array $prixUnitaire
     * @param array $refsValide
     * @return ?DemandeAppro
     */
    private function validerDemandeApproAvecLignes(string $numDa, int $numeroVersion, array $prixUnitaire = [], array $refsValide = []): ?DemandeAppro
    {
        $em = $this->getEntityManager();
        $user = $this->getUser();
        $nomutilisateur = $user->getNomUtilisateur();

        /** @var DemandeAppro|null $da */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        if (!$da) return null;

        // 1. Mise à jour de la DA
        $da
            ->setEstValidee(true)
            ->setValidateur($user)
            ->setValidePar($nomutilisateur)
            ->setStatutDal(DemandeAppro::STATUT_VALIDE);
        $em->persist($da);

        // 2. Mise à jour des lignes DAL
        /** @var iterable<DemandeApproL> $dals les lignes de DAL dernière version */
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersion]);
        foreach ($dals as $dal) {
            $dal
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(DemandeAppro::STATUT_VALIDE);

            if (isset($prixUnitaire[$dal->getNumeroLigne()])) {
                $dal->setPrixUnitaire($prixUnitaire[$dal->getNumeroLigne()]);
            }

            $em->persist($dal);
        }

        // 3. Mise à jour des lignes DALR
        /** @var iterable<DemandeApproLR> $dalrs les lignes de DALR correspondant au numéro de la DA $numDa */
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);
        foreach ($dalrs as $dalr) {
            $dalr
                ->setEstValidee(true)
                ->setValidePar($nomutilisateur)
                ->setStatutDal(DemandeAppro::STATUT_VALIDE);

            $this->mettreAJourChoixDalr($dalr, $refsValide);

            $em->persist($dalr);
        }

        return $da;
    }

    private function mettreAJourChoixDalr(DemandeApproLR $dalr, array $refsValide): void
    {
        if (empty($refsValide)) return;

        $dalr->setChoix(false);

        $numeroLigne = $dalr->getNumeroLigne();
        $numeroLigneTableau = $dalr->getNumLigneTableau();

        if (isset($refsValide[$numeroLigne]) && $numeroLigneTableau === $refsValide[$numeroLigne]) {
            $dalr->setChoix(true);
        }
    }
}
