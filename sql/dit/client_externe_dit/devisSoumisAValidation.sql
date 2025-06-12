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
ADD montantForfait DECIMAL(18, 2)

ALTER TABLE devis_soumis_a_validation
ADD natureOperation VARCHAR(3)

ALTER TABLE devis_soumis_a_validation 
ADD devisVenteOuForfait VARCHAR(15)

EXEC sp_rename 'demande_intervention.devis_valide',
'statut_devis',
'COLUMN';

ALTER TABLE demande_intervention
ALTER COLUMN statut_devis VARCHAR(50)

ALTER TABLE devis_soumis_a_validation 
ADD devise VARCHAR(10)

ALTER TABLE devis_soumis_a_validation
ADD montantVente DECIMAL(18, 2)

ALTER TABLE devis_soumis_a_validation
ADD num_migr INT


--12/03/2025
ALTER TABLE devis_soumis_a_validation
ADD montantRevient DECIMAL(18, 2)

ALTER TABLE devis_soumis_a_validation
ADD margeRevient INT
--17/03/2025
ALTER TABLE devis_soumis_a_validation
ADD type VARCHAR(5)

--19/03/2025
ALTER TABLE devis_soumis_a_validation
ADD nombreLignePiece INT