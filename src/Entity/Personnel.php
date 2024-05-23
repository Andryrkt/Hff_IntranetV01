<?php

namespace  App\Entity;


use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="Agence_service_autorise")
 * @ORM\Entity(repositoryClass=AgenceServiceAutoriserRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class AgenceServiceAutoriser 
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
   private int $Matricule;

   /**
    * @ORM\Column(type="string", length="200")
    *
    * @var string
    */
   private string $Nom;

   /**
    * @ORM\Column(type="string", length="4")
    *
    * @var string
    */
   private string $Code_AgenceService_Sage;

   /**
    * @ORM\Column(type="integer")
    *
    * @var integer
    */
   private int $Numero_Fournisseur_IRIUM;

   /**
    * @ORM\Column(type="integer")
    *
    * @var integer
    */
   private int $Code_AgenceService_IRIUM;

   /**
    * @ORM\Column(type="string", length="10")
    *
    * @var string
    */
   private string $Numero_Telephone;

   /**
    * @ORM\Column(type="integer")
    *
    * @var integer
    */
   private int $Numero_Compte_Bancaire;

   /**
    * @ORM\Column(type="DateTime")
    *
    * @var DateTime
    */
   private DateTime $Date_creation;


/**
 * @ORM\Column(type="string", length="50")
 *
 * @var string
 */
   private string $Libelle_AgenceService_Sage;

   /**
    * @ORM\Column(type="string", length="4")
    *
    * @var string
    */
   private string $Code_Service_Agence_IRIUM;

   /**
    * @ORM\Column(type="string", length="50")
    *
    * @var string
    */
   private string $Libelle_Service_Agence_IRIUM;

   /**
    * @ORM\Column(type="string", length="100")
    *
    * @var string
    */
   private string $Prenoms;

    /**
     * @ORM\Column(style="string", length="10")
     *
     * @var string
     */
   private string $Qualification;


   
    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->Date_creation = new \DateTime();
    }

 

    /**
     * Get the value of id
     *
     * @return  integer
     */ 
    public function getId()
    {
        return $this->id;
    }

   /**
    * Get the value of Matricule
    */ 
   public function getMatricule()
   {
      return $this->Matricule;
   }

   /**
    * Set the value of Matricule
    *
    * @return  self
    */ 
   public function setMatricule($Matricule)
   {
      $this->Matricule = $Matricule;

      return $this;
   }

   /**
    * Get the value of Nom
    */ 
   public function getNom()
   {
      return $this->Nom;
   }

   /**
    * Set the value of Nom
    *
    * @return  self
    */ 
   public function setNom($Nom)
   {
      $this->Nom = $Nom;

      return $this;
   }

   /**
    * Get the value of Code_AgenceService_Sage
    */ 
   public function getCodeAgenceServiceSage()
   {
      return $this->Code_AgenceService_Sage;
   }

   /**
    * Set the value of Code_AgenceService_Sage
    *
    * @return  self
    */ 
   public function setCodeAgenceServiceSage($Code_AgenceService_Sage)
   {
      $this->Code_AgenceService_Sage = $Code_AgenceService_Sage;

      return $this;
   }

   /**
    * Get the value of Numero_Fournisseur_IRIUM
    */ 
   public function getNumeroFournisseurIRIUM()
   {
      return $this->Numero_Fournisseur_IRIUM;
   }

   /**
    * Set the value of Numero_Fournisseur_IRIUM
    *
    * @return  self
    */ 
   public function setNumeroFournisseurIRIUM($Numero_Fournisseur_IRIUM)
   {
      $this->Numero_Fournisseur_IRIUM = $Numero_Fournisseur_IRIUM;

      return $this;
   }

   /**
    * Get the value of Code_AgenceService_IRIUM
    */ 
   public function getCodeAgenceServiceIRIUM()
   {
      return $this->Code_AgenceService_IRIUM;
   }

   /**
    * Set the value of Code_AgenceService_IRIUM
    *
    * @return  self
    */ 
   public function setCodeAgenceServiceIRIUM($Code_AgenceService_IRIUM)
   {
      $this->Code_AgenceService_IRIUM = $Code_AgenceService_IRIUM;

      return $this;
   }

   /**
    * Get the value of Numero_Telephone
    */ 
   public function getNumeroTelephone()
   {
      return $this->Numero_Telephone;
   }

   /**
    * Set the value of Numero_Telephone
    *
    * @return  self
    */ 
   public function setNumeroTelephone($Numero_Telephone)
   {
      $this->Numero_Telephone = $Numero_Telephone;

      return $this;
   }

   /**
    * Get the value of Numero_Compte_Bancaire
    */ 
   public function getNumeroCompteBancaire()
   {
      return $this->Numero_Compte_Bancaire;
   }

   /**
    * Set the value of Numero_Compte_Bancaire
    *
    * @return  self
    */ 
   public function setNumeroCompteBancaire($Numero_Compte_Bancaire)
   {
      $this->Numero_Compte_Bancaire = $Numero_Compte_Bancaire;

      return $this;
   }

   /**
    * Get the value of Date_creation
    */ 
   public function getDatecreation()
   {
      return $this->Date_creation;
   }

   /**
    * Set the value of Date_creation
    *
    * @return  self
    */ 
   public function setDatecreation($Date_creation)
   {
      $this->Date_creation = $Date_creation;

      return $this;
   }

   /**
    * Get the value of Libelle_AgenceService_Sage
    */ 
   public function getLibelleAgenceServiceSage()
   {
      return $this->Libelle_AgenceService_Sage;
   }

   /**
    * Set the value of Libelle_AgenceService_Sage
    *
    * @return  self
    */ 
   public function setLibelleAgenceServiceSage($Libelle_AgenceService_Sage)
   {
      $this->Libelle_AgenceService_Sage = $Libelle_AgenceService_Sage;

      return $this;
   }

   /**
    * Get the value of Code_Service_Agence_IRIUM
    */ 
   public function getCodeServiceAgenceIRIUM()
   {
      return $this->Code_Service_Agence_IRIUM;
   }

   /**
    * Set the value of Code_Service_Agence_IRIUM
    *
    * @return  self
    */ 
   public function setCodeServiceAgenceIRIUM($Code_Service_Agence_IRIUM)
   {
      $this->Code_Service_Agence_IRIUM = $Code_Service_Agence_IRIUM;

      return $this;
   }

   /**
    * Get the value of Prenoms
    */ 
   public function getPrenoms()
   {
      return $this->Prenoms;
   }

   /**
    * Set the value of Prenoms
    *
    * @return  self
    */ 
   public function setPrenoms($Prenoms)
   {
      $this->Prenoms = $Prenoms;

      return $this;
   }

   /**
    * Get the value of Qualification
    */ 
   public function getQualification()
   {
      return $this->Qualification;
   }

   /**
    * Set the value of Qualification
    *
    * @return  self
    */ 
   public function setQualification($Qualification)
   {
      $this->Qualification = $Qualification;

      return $this;
   }

   /**
    * Get the value of Libelle_Service_Agence_IRIUM
    *
    * @return  string
    */ 
   public function getLibelle_Service_Agence_IRIUM()
   {
      return $this->Libelle_Service_Agence_IRIUM;
   }

   /**
    * Set the value of Libelle_Service_Agence_IRIUM
    *
    * @param  string  $Libelle_Service_Agence_IRIUM
    *
    * @return  self
    */ 
   public function setLibelle_Service_Agence_IRIUM(string $Libelle_Service_Agence_IRIUM)
   {
      $this->Libelle_Service_Agence_IRIUM = $Libelle_Service_Agence_IRIUM;

      return $this;
   }
}