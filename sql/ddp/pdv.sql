CREATE TABLE type_demande_paiement
(
    id INT IDENTITY (1, 1),
    code_type_demande VARCHAR(3),
    libelle_type_demande VARCHAR(50),
    description_type_demande VARCHAR(8),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3),
    CONSTRAINT PK_type_demande_paiement PRIMARY KEY (id, code_type_demande)
);

CREATE TABLE document_demande_paiement
(
    id INT IDENTITY (1, 1),
    numero_demande_paiement VARCHAR(11),
    type_document_id int,
    nom_fichier VARCHAR(255),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3),
    CONSTRAINT PK_document_demande_paiement PRIMARY KEY (id)
);

CREATE TABLE demande_paiement
(
    id INT IDENTITY (1, 1),
    numero_demande_paiement VARCHAR(11),
    type_demande_id int,
    numero_fournisseur VARCHAR(7),
    rib_fournisseur VARCHAR(50),
    beneficiaire VARCHAR(50),
    motif VARCHAR(255),
    agence_a_debiter VARCHAR(2),
    service_a_debiter VARCHAR(3),
    statut VARCHAR(50),
    adresse_mail_demandeur VARCHAR(100),
    demandeur VARCHAR(100),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3),
    CONSTRAINT PK_demande_paiement PRIMARY KEY (id, numero_demande_paiement)
);


CREATE TABLE type_demande
(
    id INT IDENTITY (1, 1),
    code_type_demande VARCHAR(3),
    libelle_type_demande VARCHAR(100),
    description VARCHAR(255),
    CONSTRAINT PK_type_demande PRIMARY KEY (id)
);

INSERT INTO type_document
    (typeDocument, date_creation, date_modification, heure_creation, heure_modification, libelle_document)
VALUES('SW', '2025-01-10', '2025-01-10', '10:32:16.6800000', '10:32:16.6800000', 'SWIFT');

INSERT INTO applications
    (nom, code_app, date_creation, date_modification)
VALUES
    ('DEMANDE PAIEMENT', 'DDP', '2025-02-10', '2025-02-10', 'DDP25029999')

INSERT INTO type_demande
    (code_type_demande, libelle_type_demande, description)
VALUES
    ('DPA', 'Demande de paiement à l''avance', null),
    ('DPL', 'Demande de paiement après livraison', null)

CREATE TABLE demande_paiement_ligne
(
    id INT IDENTITY (1, 1),
    numero_demande_paiement VARCHAR(11),
    numero_ligne int,
    numero_commande VARCHAR(50),
    numero_facture VARCHAR(50),
    montant_facture DECIMAL(18, 2),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3),
    CONSTRAINT PK_demande_paiement_ligne PRIMARY KEY (id)
);