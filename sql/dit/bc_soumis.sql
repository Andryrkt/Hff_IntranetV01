CREATE TABLE bc_soumis (
    id INT IDENTITY (1, 1),
    numeroDit VARCHAR(11),
    numeroDevis VARCHAR(8),
    numeroBc VARCHAR(15),
    numeroVersion INT,
    dateBc DATE,
    dateDevis DATE,
    montantDevis DECIMAL(18, 2),
    dateHeureSoumission DATETIME2,
    CONSTRAINT PK_bc_soumis PRIMARY KEY (id)
);

INSERT INTO type_document(typeDocument, date_creation, date_modification, heure_creation, heure_modification, libelle_document)
VALUES ('BC', '2025-01-06', '2025-01-06', '10:48', '10:48', 'BON DE COMMANDE')

ALTER TABLE users
ADD num_tel VARCHAR(10)

ALTER TABLE users
ADD poste VARCHAR(50)


CREATE TABLE contact_agence_ate (
    id INT IDENTITY (1, 1),
    agence VARCHAR(2),
    matricule VARCHAR(5)
    CONSTRAINT PK_contact_agence_ate PRIMARY KEY (id)
);