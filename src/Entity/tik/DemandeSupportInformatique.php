<?php
namespace App\Entity\tik;

use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass=DemandeSupportInformatiqueRepository::class)
 * @ORM\Table(name="Demande_Support_Informatique")
 * @ORM\HasLifecyleCallbacks
 */
class DemandeSupportInformatique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Support_Informatique")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=1000, name="Mail_Demandeur")
     */
    private string $Mail_Demandeur;

    /**
     * @ORM\Column(type="integer", name="ID_TKI_Categorie")
     */
    private int $Categorie;

    /**
     * @ORM\Column(type="integer", name="ID_TKI_Sous_Categorie")
     */
    private int $Sous_Categorie;

    /**
     * @ORM\Column(type="integer", nullable=true, name="ID_TKI_Autres_Categorie")
     */
    private ?int $Autres_Categorie = null;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Emetteur")
     */
    private string $Agence_Emetteur;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Debiteur")
     */
    private string $Agence_Debiteur;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Debiteur")
     */
    private string $Service_Emetteur;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Debiteur")
     */
    private string $Service_Debiteur;

    /**
     * @ORM\Column(type="string", length=200, nullable=true, name="Mail_Intervenant")
     */
    private ?string $Mail_Intervenant = null;

    /**
     * @ORM\Column(type="string", length=200, name="Objet_Demande")
     */
    private string $Objet_Demande;

    /**
     * @ORM\Column(type="text", name="Detail_Demande")
     */
    private string $Detail_Demande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe1")
     */
    private ?string $Piece_Jointe1 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe2")
     */
    private ?string $Piece_Jointe2 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe3")
     */
    private ?string $Piece_Jointe3 = null;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Planning")
     */
    private DateTime $Date_Fin_Planning;

    /**
     * @ORM\Column(type="integer", name="ID_Niveau_Urgence")
     */
    private int $Niveau_Urgence;

    /**
     * @ORM\Column(type="string", length=50, name="Parc_Informatique")
     */
    private string $Parc_Informatique;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Souhaitee")
     */
    private DateTime $Date_Fin_Souhaitee;


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Mail_Demandeur
     */ 
    public function getMail_Demandeur()
    {
        return $this->Mail_Demandeur;
    }

    /**
     * Set the value of Mail_Demandeur
     *
     * @return  self
     */ 
    public function setMail_Demandeur($Mail_Demandeur)
    {
        $this->Mail_Demandeur = $Mail_Demandeur;

        return $this;
    }

    /**
     * Get the value of Mail_Intervenant
     */ 
    public function getMail_Intervenant()
    {
        return $this->Mail_Intervenant;
    }

    /**
     * Set the value of Mail_Intervenant
     *
     * @return  self
     */ 
    public function setMail_Intervenant($Mail_Intervenant)
    {
        $this->Mail_Intervenant = $Mail_Intervenant;

        return $this;
    }

    /**
     * Get the value of Objet_Demande
     */ 
    public function getObjet_Demande()
    {
        return $this->Objet_Demande;
    }

    /**
     * Set the value of Objet_Demande
     *
     * @return  self
     */ 
    public function setObjet_Demande($Objet_Demande)
    {
        $this->Objet_Demande = $Objet_Demande;

        return $this;
    }

    /**
     * Get the value of Detail_Demande
     */ 
    public function getDetail_Demande()
    {
        return $this->Detail_Demande;
    }

    /**
     * Set the value of Detail_Demande
     *
     * @return  self
     */ 
    public function setDetail_Demande($Detail_Demande)
    {
        $this->Detail_Demande = $Detail_Demande;

        return $this;
    }

    /**
     * Get the value of Piece_Jointe1
     */ 
    public function getPiece_Jointe1()
    {
        return $this->Piece_Jointe1;
    }

    /**
     * Set the value of Piece_Jointe1
     *
     * @return  self
     */ 
    public function setPiece_Jointe1($Piece_Jointe1)
    {
        $this->Piece_Jointe1 = $Piece_Jointe1;

        return $this;
    }

    /**
     * Get the value of Piece_Jointe2
     */ 
    public function getPiece_Jointe2()
    {
        return $this->Piece_Jointe2;
    }

    /**
     * Set the value of Piece_Jointe2
     *
     * @return  self
     */ 
    public function setPiece_Jointe2($Piece_Jointe2)
    {
        $this->Piece_Jointe2 = $Piece_Jointe2;

        return $this;
    }

    /**
     * Get the value of Piece_Jointe3
     */ 
    public function getPiece_Jointe3()
    {
        return $this->Piece_Jointe3;
    }

    /**
     * Set the value of Piece_Jointe3
     *
     * @return  self
     */ 
    public function setPiece_Jointe3($Piece_Jointe3)
    {
        $this->Piece_Jointe3 = $Piece_Jointe3;

        return $this;
    }

    /**
     * Get the value of Date_Fin_Planning
     */ 
    public function getDate_Fin_Planning()
    {
        return $this->Date_Fin_Planning;
    }

    /**
     * Set the value of Date_Fin_Planning
     *
     * @return  self
     */ 
    public function setDate_Fin_Planning($Date_Fin_Planning)
    {
        $this->Date_Fin_Planning = $Date_Fin_Planning;

        return $this;
    }

    /**
     * Get the value of Parc_Informatique
     */ 
    public function getParc_Informatique()
    {
        return $this->Parc_Informatique;
    }

    /**
     * Set the value of Parc_Informatique
     *
     * @return  self
     */ 
    public function setParc_Informatique($Parc_Informatique)
    {
        $this->Parc_Informatique = $Parc_Informatique;

        return $this;
    }

    /**
     * Get the value of Date_Fin_Souhaitee
     */ 
    public function getDate_Fin_Souhaitee()
    {
        return $this->Date_Fin_Souhaitee;
    }

    /**
     * Set the value of Date_Fin_Souhaitee
     *
     * @return  self
     */ 
    public function setDate_Fin_Souhaitee($Date_Fin_Souhaitee)
    {
        $this->Date_Fin_Souhaitee = $Date_Fin_Souhaitee;

        return $this;
    }

    /**
     * Get the value of Niveau_Urgence
     */ 
    public function getNiveau_Urgence()
    {
        return $this->Niveau_Urgence;
    }

    /**
     * Set the value of Niveau_Urgence
     *
     * @return  self
     */ 
    public function setNiveau_Urgence($Niveau_Urgence)
    {
        $this->Niveau_Urgence = $Niveau_Urgence;

        return $this;
    }

    /**
     * Get the value of Categorie
     */ 
    public function getCategorie()
    {
        return $this->Categorie;
    }

    /**
     * Set the value of Categorie
     *
     * @return  self
     */ 
    public function setCategorie($Categorie)
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    /**
     * Get the value of Sous_Categorie
     */ 
    public function getSous_Categorie()
    {
        return $this->Sous_Categorie;
    }

    /**
     * Set the value of Sous_Categorie
     *
     * @return  self
     */ 
    public function setSous_Categorie($Sous_Categorie)
    {
        $this->Sous_Categorie = $Sous_Categorie;

        return $this;
    }

    /**
     * Get the value of Autres_Categorie
     */ 
    public function getAutres_Categorie()
    {
        return $this->Autres_Categorie;
    }

    /**
     * Set the value of Autres_Categorie
     *
     * @return  self
     */ 
    public function setAutres_Categorie($Autres_Categorie)
    {
        $this->Autres_Categorie = $Autres_Categorie;

        return $this;
    }

    /**
     * Get the value of Agence_Emetteur
     */ 
    public function getAgence_Emetteur()
    {
        return $this->Agence_Emetteur;
    }

    /**
     * Set the value of Agence_Emetteur
     *
     * @return  self
     */ 
    public function setAgence_Emetteur($Agence_Emetteur)
    {
        $this->Agence_Emetteur = $Agence_Emetteur;

        return $this;
    }

    /**
     * Get the value of Agence_Debiteur
     */ 
    public function getAgence_Debiteur()
    {
        return $this->Agence_Debiteur;
    }

    /**
     * Set the value of Agence_Debiteur
     *
     * @return  self
     */ 
    public function setAgence_Debiteur($Agence_Debiteur)
    {
        $this->Agence_Debiteur = $Agence_Debiteur;

        return $this;
    }

    /**
     * Get the value of Service_Debiteur
     */ 
    public function getService_Debiteur()
    {
        return $this->Service_Debiteur;
    }

    /**
     * Set the value of Service_Debiteur
     *
     * @return  self
     */ 
    public function setService_Debiteur($Service_Debiteur)
    {
        $this->Service_Debiteur = $Service_Debiteur;

        return $this;
    }
}