
CREATE TABLE Demande_Appro_L
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) not null,
    num_ligne int not null,
    art_rempl BIT,
    qte_dem int,
    qte_dispo int,
    art_constp varchar(3),
    art_refp varchar(50),
    art_desi varchar(100) not null,
    code_fams1 varchar(10),
    art_fams1 varchar(50),
    code_fams2 varchar(50),
    art_fams2 varchar(50),
    numero_fournisseur varchar(7) not null,
    nom_fournisseur varchar(50) not null,
    date_fin_souhaitee_l DATETIME2(0),
    commentaire varchar(1000),
    statut_dal varchar(50),
    catalogue BIT,
    demande_appro_id int not null,
    CONSTRAINT PK_Demande_Appro_L PRIMARY KEY (id)
)

ALTER TABLE Demande_Appro_L
ADD est_validee bit DEFAULT 0

ALTER TABLE Demande_Appro_L
ADD est_modifier bit DEFAULT 0

ALTER TABLE Demande_Appro_L
ADD date_creation DATETIME2(0)

ALTER TABLE Demande_Appro_L
ADD date_modification DATETIME2(0)

ALTER TABLE Demande_Appro_L
ALTER COLUMN code_fams2 VARCHAR(50)

ALTER TABLE Demande_Appro_L
ADD valide_par VARCHAR(50)

ALTER TABLE Demande_Appro_L
ADD numero_version INT DEFAULT 0

ALTER TABLE Demande_Appro_L
ADD edit INT DEFAULT 0

ALTER TABLE Demande_Appro_L
ADD deleted BIT DEFAULT 0;