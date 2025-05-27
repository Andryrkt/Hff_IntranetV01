
CREATE TABLE Demande_Appro_L_R
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) not null,
    num_ligne_dem int not null,
    qte_dem int not null,
    qte_dispo int,
    art_constp varchar(3) not null,
    art_refp varchar(50) not null,
    art_desi varchar(100) not null,
    art_fams1 varchar(50) not null,
    art_fams2 varchar(50) not null,
    numero_fournisseur varchar(7) not null,
    nom_fournisseur varchar(50) not null,
    PU VARCHAR(100) NOT NULL,
    total VARCHAR(100) NOT NULL,
    conditionnement VARCHAR(10) NOT NULL,
    motif VARCHAR(1000) NOT NULL,
    demande_appro_l_id int not null,
    CONSTRAINT PK_Demande_Appro_L_R PRIMARY KEY (id)
)

ALTER TABLE Demande_Appro_L_R
ADD est_validee bit DEFAULT 0

ALTER TABLE Demande_Appro_L_R
ADD choix bit DEFAULT 0

ALTER TABLE Demande_Appro_L_R
ADD date_creation DATETIME2(0)

ALTER TABLE Demande_Appro_L_R
ADD date_modification DATETIME2(0)

ALTER TABLE Demande_Appro_L_R
ADD num_ligne_tableau INT

ALTER TABLE Demande_Appro_L_R
ADD code_fams1 VARCHAR(10)

ALTER TABLE Demande_Appro_L_R
ADD code_fams2 VARCHAR(50)

ALTER TABLE Demande_Appro_L_R
ALTER COLUMN art_fams1 varchar(50)

ALTER TABLE Demande_Appro_L_R
ALTER COLUMN art_fams2 varchar(50)

ALTER TABLE Demande_Appro_L_R
ADD valide_par VARCHAR(50)


ALTER TABLE Demande_Appro_L_R
ADD deleted BIT DEFAULT 0;