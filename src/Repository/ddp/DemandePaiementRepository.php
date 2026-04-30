<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;
use App\Entity\admin\ddp\DdpSearch;
use App\Entity\admin\utilisateur\User;
use App\Service\TableauEnStringService;

class DemandePaiementRepository extends EntityRepository
{
    public function CompteNbrligne($numerofournisseur)
    {
        $nbrLigne = $this->createQueryBuilder('ddp')
            ->select('COUNT(ddp.numeroFournisseur)')
            ->where('ddp.numeroFournisseur = :numFrn')
            ->andWhere('ddp.statut != :statut')
            ->setParameters([
                'numFrn' => $numerofournisseur,
                'statut' => 'Annulé'
            ])
            ->getQuery()
            ->getSingleScalarResult();;

        return $nbrLigne ? $nbrLigne : 0;
    }

    public function recuperation_numFrs_numCde($numeroDdp)
    {
        $data = $this->createQueryBuilder('ddp')
            ->select('ddp.numeroFournisseur, ddp.numeroCommande')
            ->where('ddp.numeroDdp = :numDdp')
            ->setParameters([
                'numDdp' => $numeroDdp
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if ($data) {
            return [
                'numeroFournisseur' => $data['numeroFournisseur'],
                'numeroCommande' => is_array($data['numeroCommande'])
                    ? TableauEnStringService::TableauEnString(",", $data['numeroCommande'])
                    : $data['numeroCommande']
            ];
        }

        return null;
        return $data;
    }

    public function findNumeroVersionMax(string $numDdp)
    {
        $numeroVersionMax = $this->createQueryBuilder('Ddp')
            ->select('MAX(Ddp.numeroVersion)')
            ->where('Ddp.numeroDdp = :numDdp')
            ->setParameter('numDdp', $numDdp)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    public function findDemandePaiement(DdpSearch $ddpSearch, string $codeAgence, string $codeService, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur, bool $multisuccursale)
    {
        $qb = $this->createQueryBuilder('d')
            ->join(User::class, 'u', 'WITH', 'd.demandeur = u.nom_utilisateur');

        // Sous-requête imbriquée dans la clause WHERE
        $qb->where(
            'd.numeroVersion = (
                SELECT MAX(dp2.numeroVersion)
                FROM App\Entity\ddp\DemandePaiement dp2
                WHERE dp2.numeroDdp = d.numeroDdp
                AND dp2.agenceDebiter = d.agenceDebiter
                AND dp2.serviceDebiter = d.serviceDebiter
            )'
        );

        if (!empty($ddpSearch->getAgence())) {
            $qb->andWhere('d.agenceDebiter = :agenceDebiter')
                ->setParameter('agenceDebiter', $ddpSearch->getAgence());
        }
        if (!empty($ddpSearch->getService())) {
            $qb->andWhere('d.serviceDebiter = :serviceDebiter')
                ->setParameter('serviceDebiter', $ddpSearch->getService());
        }
        if (!empty($ddpSearch->getTypeDemande())) {
            $qb->andWhere('d.typeDemandeId = :typeDemandeId')
                ->setParameter('typeDemandeId', $ddpSearch->getTypeDemande()->getId());
        }
        if (!empty($ddpSearch->getNumDdp())) {
            $qb->andWhere('d.numeroDdp = :numeroDdp')
                ->setParameter('numeroDdp', $ddpSearch->getNumDdp());
        }
        if (!empty($ddpSearch->getNumCommande())) {
            $qb->andWhere('d.numeroCommande LIKE :numeroCommande')
                ->setParameter('numeroCommande', '%' . $ddpSearch->getNumCommande() . '%');
        }
        if (!empty($ddpSearch->getNumFacture())) {
            $qb->andWhere('d.numeroFacture LIKE :numeroFacture')
                ->setParameter('numeroFacture', '%' . $ddpSearch->getNumFacture() . '%');
        }

        if (!empty($ddpSearch->getUtilisateur())) {
            $qb->andWhere('d.demandeur = :demandeur')
                ->setParameter('demandeur', $ddpSearch->getUtilisateur());
        }

        if (!empty($ddpSearch->getStatut())) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $ddpSearch->getStatut());
        }

        if (!empty($ddpSearch->getDateDebut())) {
            $qb->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $ddpSearch->getDateDebut());
        }

        if (!empty($ddpSearch->getDateFin())) {
            $qb->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $ddpSearch->getDateFin());
        }

        if (!empty($ddpSearch->getFournisseur())) {
            $qb->andWhere('d.numeroFournisseur = :numFournisseur')
                ->setParameter('numFournisseur', explode('-', $ddpSearch->getFournisseur())[0]);
        }

        if (!$multisuccursale) {
            // Condition sur les couples agences-services
            $this->conditionAgenceService($qb, $codeAgence, $codeService, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);
        }

        $qb->orderBy('d.dateCreation', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getnumCde()
    {
        return  $this->createQueryBuilder('d')
            ->select('d.numeroCommande')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    private function conditionAgenceService($queryBuilder, string $codeAgence, string $codeService, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $ORX = $queryBuilder->expr()->orX();

        // 1- Emetteur du DDP : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('u.codeAgenceUser', ':agEmetteur'),
                $queryBuilder->expr()->eq('u.codeServiceUser', ':servEmetteur')
            )
        );
        $queryBuilder->setParameter('agEmetteur', $codeAgence);
        $queryBuilder->setParameter('servEmetteur', $codeService);

        // 2- Debiteur du DDP : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('u.codeAgenceUser', ':agDebiteur'),
                $queryBuilder->expr()->eq('u.codeServiceUser', ':servDebiteur')
            )
        );
        $queryBuilder->setParameter('agDebiteur', $codeAgence);
        $queryBuilder->setParameter('servDebiteur', $codeService);

        // 3- Emetteur et Débiteur : agence et service autorisés du profil
        if (!empty($agenceServiceAutorises)) {
            $orX1 = $queryBuilder->expr()->orX(); // Pour émetteur
            $orX2 = $peutVoirListeAvecDebiteur ? $queryBuilder->expr()->orX() : null; // Pour débiteur : n'autoriser que si le profil peut voir la liste avec le débiteur
            foreach ($agenceServiceAutorises as $i => $tab) {
                $orX1->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('u.codeAgenceUser', ':agEmetteur_' . $i),
                        $queryBuilder->expr()->eq('u.codeServiceUser', ':servEmetteur_' . $i)
                    )
                );
                $queryBuilder->setParameter('agEmetteur_' . $i, $tab['agence_code']);
                $queryBuilder->setParameter('servEmetteur_' . $i, $tab['service_code']);
                if ($orX2) {
                    $orX2->add(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('d.agenceDebiter', ':agDebiteur_' . $i),
                            $queryBuilder->expr()->eq('d.serviceDebiter', ':servDebiteur_' . $i)
                        )
                    );
                    $queryBuilder->setParameter('agDebiteur_' . $i, $tab['agence_code']);
                    $queryBuilder->setParameter('servDebiteur_' . $i, $tab['service_code']);
                }
            }

            $ORX->add($orX1);
            if ($orX2) $ORX->add($orX2);
        }

        $queryBuilder->andWhere($ORX);
    }
}
