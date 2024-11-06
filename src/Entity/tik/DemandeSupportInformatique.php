<?php
namespace App\Entity\tik;

use Symfony\Component\Validator\Constraints\DateTime;
use App\Repository\tik\DemandeSupportInformatiqueRepository;

/**
 * @ORM\Entity(repositoryClass=DemandeSupportInformatiqueRepository::class)
 * @ORM\Table(name="Demande_Support_Informatique")Date_Fin
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
     * @ORM\Column(type="datetime", name="Date_Creation")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=50, name="Utilisateur_Demandeur")
     */
    private string $utilisateurDemandeur;

    /**
     * @ORM\Column(type="string", length=50, name="Mail_Demandeur")
     */
    private string $mailDemandeur;

    /**
     * @ORM\Column(type="string", length=1000, name="Mail_En_Copie")
     */
    private string $mailEnCopie;

    /**
     * @ORM\Column(type="string", length=2, name="Code_Societe")
     */
    private string $codeSociete;

    /**
     * @ORM\Column(type="integer", name="ID_TKI_Categorie")
     * TODO : RELATION A FAITE
     */
    private int $categorie;

    /**
     * @ORM\Column(type="integer", name="ID_TKI_Sous_Categorie")
     * TODO : RELATION A FAITE
     */
    private int $sousCategorie;

    /**
     * @ORM\Column(type="integer", nullable=true, name="ID_TKI_Autres_Categorie")
     * TODO : RELATION A FAITE
     */
    private ?int $autresCategorie = null;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Emetteur")
     */
    private string $agenceServiceEmetteur;

    /**
     * @ORM\Column(type="string", length=100, name="AgenceService_Debiteur")
     */
    private string $agenceServiceDebiteur;

    /**
     * @ORM\Column(type="string", length=100, name="Mail_Intervenant")
     */
    private string $nomIntervenant;

    /**
     * @ORM\Column(type="string", length=100, name="Nom_Intervenant")
     */
    private ?string $mailIntervenant = null;

    /**
     * @ORM\Column(type="string", length=100, name="Objet_Demande")
     */
    private string $objetDemande;

    /**
     * @ORM\Column(type="string", length=5000, name="Detail_Demande")
     */
    private string $detailDemande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe1")
     */
    private ?string $pieceJointe1 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe2")
     */
    private ?string $pieceJointe2 = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="Piece_Jointe3")
     */
    private ?string $pieceJointe3 = null;

     /**
     * @ORM\Column(type="datetime", name="Date_Deb_Planning")
     */
    private $dateDebutPlanning;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Planning")
     */
    private $dateFinPlanning;

    /**
     * @ORM\Column(type="integer", name="ID_Niveau_Urgence")
     * TODO : RELATIION A FAIRE
     */
    private int $niveauUrgence;

    /**
     * @ORM\Column(type="string", length=50, name="Parc_Informatique")
     */
    private string $parcInformatique;

    /**
     * @ORM\Column(type="datetime", name="Date_Fin_Souhaitee")
     */
    private $dateFinSouhaitee;


    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

   
}