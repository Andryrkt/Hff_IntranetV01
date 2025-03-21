
CREATE TABLE Demande_Appro_L_R(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) not null,
    num_ligne_dem int(11) not null,
    qte_dem int(11) not null,
    qte_dispo int(11),
    art_constp varchar(3) not null,
    art_refp varchar(50) not null,
    art_desi varchar(100) not null,
    art_fams1 varchar(50) not null,
    art_fams2 varchar(50) not null,
    numero_fournisseur varchar(7) not null,
    nom_fournisseur varchar(50) not null,
    CONSTRAINT PK_Demande_Appro_L_R PRIMARY KEY (id)
)