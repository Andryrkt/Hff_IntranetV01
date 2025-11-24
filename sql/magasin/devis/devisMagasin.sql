CREATE TABLE devis_soumis_a_validation_neg
(
    id int IDENTITY(1,1) NOT NULL,
    numero_devis varchar(11) NOT NULL,
    numero_version INT NOT NULL DEFAULT 0,
    statut_dw varchar(100),
    nombre_lignes INT NOT NULL DEFAULT 0,
    montant_devis DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    devise varchar(3) NOT NULL,
    type_soumission varchar(2) NOT NULL,
    date_maj_statut DATETIME2(0) null,
    utilisateur varchar(100) not null,
    cat BIT NOT NULL DEFAULT 0,
    non_cat BIT NOT NULL DEFAULT 0,
    nom_fichier varchar(255) null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_devis_soumis_a_validation_neg PRIMARY KEY (id)
);

    ALTER TABLE devis_soumis_a_validation_neg
    ADD date_envoye_devis_client DATETIME2(0) NULL

    ALTER TABLE devis_soumis_a_validation_neg
    ADD somme_numero_lignes INT NOT NULL DEFAULT 0;

    ALTER TABLE devis_soumis_a_validation_neg
    ADD date_pointage DATETIME2(0) NULL

    ALTER TABLE devis_soumis_a_validation_neg
    ADD  tache_validateur VARCHAR(50) NULL;

    ALTER TABLE devis_soumis_a_validation_neg
    ADD statut_bc VARCHAR(100) NULL;

    ALTER TABLE devis_soumis_a_validation_neg
    ADD relance VARCHAR(50) NULL;

    ALTER TABLE devis_soumis_a_validation_neg
    ADD est_validation_pm BIT DEFAULT 0;

    ALTER TABLE devis_soumis_a_validation_neg
    ADD date_bc DATETIME2(0) NULL