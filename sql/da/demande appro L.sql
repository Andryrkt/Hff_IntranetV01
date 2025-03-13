
CREATE TABLE Demande_Appro(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) not null,
    num_ligne int(11) not null,
    art_rempl BIT,
    qte_dispo int(11),
    art_constp varchar(3),
    art_refp varchar(50),
    art_desi varchar(100) not null,
    art_fams1 varchar(50),
    art_fams2 varchar(50),
    numero_fournisseur varchar(7) not null,
    nom_fournisseur	varchar(50) not null,
    date_fin_souhaitee_l datetime,
    commentaire	varchar(1000),
    statut_dal	varchar(50),
    catalogue BIT,
)


