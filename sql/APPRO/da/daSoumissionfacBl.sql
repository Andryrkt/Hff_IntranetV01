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

alter table da_soumission_facture_bl add
    nom_fichier_scannee varchar(255) NULL,
    numero_livraison varchar(10) null,
    reference_bl_facture varchar(255) null,
    date_bl_facture DATETIME2(0) null,
    date_cloture_liv DATETIME2(0) null,
    numero_bap VARCHAR(11) NULL,
    statut_bap VARCHAR(100) NULL,
    date_soumission_compta DATETIME2(0) NULL,
    montant_bl_facture DECIMAL(18,2) NULL,
    montant_reception_ips DECIMAL(18,2) NULL,
    numero_demande_paiement VARCHAR(11) NULL,
    date_statut_bap DATETIME2(0) NULL,
    numero_fournisseur INT NULL,
    nom_fournisseur VARCHAR(255) NULL,
    numero_facture_fournisseur VARCHAR(255) NULL,
    est_facture_reappro BIT NULL DEFAULT 0,
    numero_facture_reappro VARCHAR(8) NULL
    ;


ALTER TABLE da_soumission_facture_bl ADD
    numero_cla VARCHAR(11) NULL
    ;

UPDATE da_soumission_facture_bl
SET est_facture_reappro = 0
WHERE est_facture_reappro IS NULL;