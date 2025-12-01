CREATE TABLE da_soumission_facture_bl
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11),
    numero_demande_dit varchar(11),
    numero_or varchar(11),
    numero_cde varchar(11),
    statut varchar(100),
    piece_joint1 varchar(255) ,
    utilisateur varchar(100),
    numero_version int,
    date_creation DATETIME2(0) ,
    date_modification DATETIME2(0),
    CONSTRAINT PK_da_soumission_facture_bl PRIMARY KEY (id)
);

alter table da_soumission_facture_bl add nom_fichier_scannee varchar(255) NULL;

alter table da_soumission_facture_bl add
    numero_livraison varchar(10) null,
    reference_bl_facture varchar(255) null,
    date_bl_facture DATETIME2(0) null,
    date_cloture_liv DATETIME2(0) null;