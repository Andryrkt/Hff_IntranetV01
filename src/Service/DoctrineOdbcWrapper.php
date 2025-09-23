<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Connexion;
use App\Model\ConnexionDote4;
use App\Model\ConnexionDote4Gcot;
use App\Model\DatabaseInformix;

/**
 * Wrapper pour rediriger les requêtes Doctrine vers les connexions ODBC
 */
class DoctrineOdbcWrapper
{
    private $entityManager;
    private $connexion;
    private $connexionDote4;
    private $connexionDote4Gcot;
    private $connexionInformix;

    public function __construct(
        EntityManagerInterface $entityManager,
        Connexion $connexion,
        ConnexionDote4 $connexionDote4,
        ConnexionDote4Gcot $connexionDote4Gcot,
        DatabaseInformix $connexionInformix
    ) {
        $this->entityManager = $entityManager;
        $this->connexion = $connexion;
        $this->connexionDote4 = $connexionDote4;
        $this->connexionDote4Gcot = $connexionDote4Gcot;
        $this->connexionInformix = $connexionInformix;
    }

    /**
     * Exécute une requête sur la base de données principale
     */
    public function query(string $sql)
    {
        return $this->connexion->query($sql);
    }

    /**
     * Exécute une requête sur la base Dote4
     */
    public function queryDote4(string $sql)
    {
        return $this->connexionDote4->query($sql);
    }

    /**
     * Exécute une requête sur la base Dote4 GCOT
     */
    public function queryDote4Gcot(string $sql)
    {
        return $this->connexionDote4Gcot->query($sql);
    }

    /**
     * Exécute une requête sur la base Informix
     */
    public function queryInformix(string $sql)
    {
        return $this->connexionInformix->query($sql);
    }

    /**
     * Obtient l'EntityManager (pour les entités uniquement)
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Obtient la connexion principale
     */
    public function getConnexion(): Connexion
    {
        return $this->connexion;
    }

    /**
     * Obtient la connexion Dote4
     */
    public function getConnexionDote4(): ConnexionDote4
    {
        return $this->connexionDote4;
    }

    /**
     * Obtient la connexion Dote4 GCOT
     */
    public function getConnexionDote4Gcot(): ConnexionDote4Gcot
    {
        return $this->connexionDote4Gcot;
    }

    /**
     * Obtient la connexion Informix
     */
    public function getConnexionInformix(): DatabaseInformix
    {
        return $this->connexionInformix;
    }
}
