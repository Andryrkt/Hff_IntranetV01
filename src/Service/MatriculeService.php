<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class MatriculeService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Récupérer les matricules disponibles
     * Cette méthode devrait être adaptée selon votre source de données (BDD, LDAP, etc.)
     */
    public function getMatriculesDisponibles(): array
    {
        // Pour l'instant, on retourne un tableau vide car le matricule est saisi manuellement
        // Dans une implémentation complète, cela pourrait provenir d'une API ou d'une base de données
        return [];
    }

    /**
     * Récupérer le nom et prénom selon le matricule
     * Cette méthode devrait être adaptée selon votre source de données (BDD, LDAP, etc.)
     */
    public function getNomPrenomsByMatricule(string $matricule): string
    {
        // Dans une implémentation réelle, cela récupérerait les données depuis
        // votre système d'utilisateur (LDAP, base de données, etc.)
        // Pour l'instant, on retourne une valeur factice

        // Exemple de récupération depuis une table d'utilisateurs ou un service externe
        // $userRepository = $this->entityManager->getRepository(Utilisateur::class);
        // $utilisateur = $userRepository->findOneBy(['matricule' => $matricule]);
        //
        // if ($utilisateur) {
        //     return $utilisateur->getNom() . ' ' . $utilisateur->getPrenom();
        // }

        return "Nom Prénom de l'utilisateur avec matricule: $matricule";
    }
}