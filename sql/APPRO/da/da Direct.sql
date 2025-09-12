
EXEC sp_rename 'Demande_Appro.nom_fichier_reference_zst', 'nom_fichier_bav', 'COLUMN';

ALTER TABLE Demande_Appro DROP COLUMN devis_achat;
ALTER TABLE Demande_Appro_L DROP COLUMN art_rempl;

CREATE TABLE da_soumis_a_validation
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) NOT NULL,
    numero_version INT DEFAULT 0,
    statut varchar(100) not null,
    date_soumission DATETIME2(0) not null,
    date_validation DATETIME2(0) null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    utilisateur varchar(100) not null,
    CONSTRAINT PK_da_soumis_a_validation PRIMARY KEY (id)
);