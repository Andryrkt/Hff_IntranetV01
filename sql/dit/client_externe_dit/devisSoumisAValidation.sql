CREATE TABLE devis_soumis_a_validation
(
    id INT IDENTITY (1, 1),
    numeroDit VARCHAR(11),
    numeroDevis VARCHAR(8),
    numeroItv INT,
    nombreLigneItv INT,
    montantItv DECIMAL(18, 2),
    numeroVersion INT,
    montantPiece DECIMAL(18, 2),
    montantMo DECIMAL(18, 2),
    montantAchatLocaux DECIMAL(18, 2),
    montantFraisDivers DECIMAL(18, 2),
    montantLubrifiants DECIMAL(18, 2),
    libellelItv VARCHAR(500),
    statut VARCHAR(50),
    dateHeureSoumission DATETIME2,
    CONSTRAINT PK_devis_soumis_a_validation PRIMARY KEY (id)
);

ALTER TABLE devis_soumis_a_validation
ADD montantForfait DECIMAL(18, 2),
natureOperation VARCHAR(3),
devisVenteOuForfait VARCHAR(15),
devise VARCHAR(10),
montantVente DECIMAL(18, 2),
num_migr INT,
montantRevient DECIMAL(18, 2),
margeRevient INT,
type VARCHAR(5),
nombreLignePiece INT


EXEC sp_rename 'demande_intervention.devis_valide',
'statut_devis',
'COLUMN';

ALTER TABLE demande_intervention
ALTER COLUMN statut_devis VARCHAR(50)


ALTER TABLE devis_soumis_a_validation
ADD tache_validateur VARCHAR(200),
observation VARCHAR(3000)

