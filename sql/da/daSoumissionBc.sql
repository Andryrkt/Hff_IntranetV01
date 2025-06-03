CREATE TABLE da_soumission_bc
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) NOT NULL,
    numero_demande_dit varchar(11) not null,
    numero_or varchar(11) not null,
    numero_cde varchar(11) not null,
    statut varchar(100) null,
    nom_fiche_bc varchar(255) not null,
    utilisateur varchar(100) not null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_da_soumission_bc PRIMARY KEY (id)
)