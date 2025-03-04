<?php

namespace App\Repository\admin\utilisateur;



use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository
{

    // Ajoutez des méthodes personnalisées ici
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** 
     * Fonction pour obtenir la liste des utilisateurs ayant un role spécifique
     * 
     * @param string $roleName nom du role à chercher
     */
    public function findByRole(string $roleName)
    {
        return $this->createQueryBuilder('u')
            ->join('u.roles', 'r') // Jointure avec la table des rôles, r: alias pour l'entité Role
            ->where('r.role_name = :roleName') // Condition sur le nom du rôle
            ->setParameter('roleName', $roleName) // Paramètre pour le rôle
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMail(string $nomUtilisateur)
    {
        return $this->createQueryBuilder('u')
            ->select('u.mail')
            ->where('u.nom_utilisateur = :nomUser')
            ->setParameter('nomUser', $nomUtilisateur)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}