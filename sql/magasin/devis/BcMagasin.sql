CREATE TABLE bc_client_soumis_neg
(
    id int IDENTITY(1,1) NOT NULL,
    numero_devis varchar(11) NOT NULL,
    numero_bc varchar(50) NOT NULL,
    montant_devis DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    montant_bc DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    numero_version INT NOT NULL DEFAULT 0,
    statut_bc varchar(100),
    observations varchar(500) null,
    utilisateur varchar(100) not null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_bc_client_soumis_neg PRIMARY KEY (id)
);

ALTER TABLE bc_client_soumis_neg
    ADD date_bc DATETIME2(0) NULL