ALTER TABLE demande_intervention ADD section_affectee VARCHAR(255)

ALTER TABLE demande_intervention ADD statut_or VARCHAR(255)

ALTER TABLE demande_intervention ADD statut_commande VARCHAR(255)

ALTER TABLE demande_intervention ADD date_validation_or DATE

ALTER TABLE demande_intervention
ADD agence_emetteur_id INT
ALTER TABLE demande_intervention
ADD service_emetteur_id INT
ALTER TABLE demande_intervention
ADD agence_debiteur_id INT
ALTER TABLE demande_intervention
ADD service_debiteur_id INT

UPDATE demande_intervention
SET
    agence_emetteur_id = CASE
        WHEN LEFT(agence_service_emmeteur, 2) = '92' THEN '11'
        WHEN LEFT(agence_service_emmeteur, 2) = '80' THEN '8'
        ELSE '0'
    END;

UPDATE demande_intervention
SET
    service_emetteur_id = CASE
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LCJ' THEN '43'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'SLR' THEN '45'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'INF' THEN '13'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'NOS' THEN '28'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAH' THEN '27'
        ELSE '0'
    END;

UPDATE demande_intervention
SET
    agence_debiteur_id = CASE
        WHEN LEFT(agence_service_debiteur, 2) = '92' THEN '11'
        WHEN LEFT(agence_service_debiteur, 2) = '80' THEN '8'
        WHEN LEFT(agence_service_debiteur, 2) = '91' THEN '10'
        ELSE '0'
    END;

UPDATE demande_intervention
SET
    service_debiteur_id = CASE
        WHEN RIGHT(agence_service_debiteur, 3) = 'LCJ' THEN '43'
        WHEN RIGHT(agence_service_debiteur, 3) = 'SLR' THEN '45'
        WHEN RIGHT(agence_service_debiteur, 3) = 'INF' THEN '13'
        WHEN RIGHT(agence_service_debiteur, 3) = 'NOS' THEN '28'
        WHEN RIGHT(agence_service_debiteur, 3) = 'MAH' THEN '27'
        ELSE '0'
    END;

UPDATE demande_intervention
SET
    internet_externe = CASE
        WHEN internet_externe = 'I' THEN 'INTERNE'
        WHEN internet_externe = 'E' THEN 'EXTERNE'
        ELSE internet_externe
    END

UPDATE demande_intervention_migration
SET
    type_document = CASE
        WHEN ID_Materiel = 18179 THEN 1
        WHEN ID_Materiel = 06412 THEN 1
        WHEN ID_Materiel = 16242 THEN 3
        WHEN ID_Materiel = 06412 THEN 2
        WHEN ID_Materiel = 16243 THEN 1
        WHEN ID_Materiel = 00978 THEN 1
        WHEN ID_Materiel = 15789 THEN 1
        WHEN ID_Materiel = 04064 THEN 1
        WHEN ID_Materiel = 15593 THEN 1
        WHEN ID_Materiel = 18049 THEN 1
        WHEN ID_Materiel = 19043 THEN 2
        WHEN ID_Materiel = 16118 THEN 1
        WHEN ID_Materiel = 16802 THEN 1
        WHEN ID_Materiel = 16803 THEN 1
        WHEN ID_Materiel = 07676 THEN 2
        WHEN ID_Materiel = 16118 THEN 1
        WHEN ID_Materiel = 17495 THEN 1
        WHEN ID_Materiel = 18118 THEN 1
        WHEN ID_Materiel = 15783 THEN 1
        ELSE ID_Materiel
    END
    -- CREATION DE TABLE COMMENTAIRE DIT OR
CREATE TABLE commentaire_dit_or (
    id INT IDENTITY (1, 1),
    utilisateur_id INT NOT NULL,
    num_dit VARCHAR(11),
    num_or VARCHAR(50),
    type_commentaire VARCHAR(3) NOT NULL,
    commentaire TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT GETDATE (),
    CONSTRAINT PK_commentaire_dit_or PRIMARY KEY (id),
    CONSTRAINT FK_commentaire_dit_or_utilisateur_id FOREIGN KEY (utilisateur_id) REFERENCES Users (id),
);

-- CREATION DE TABLE OR SOUMIS A VALIDATION
CREATE TABLE ors_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numeroDit VARCHAR(11),
    numeroOR VARCHAR(8),
    numeroItv INT,
    nombrePieceItv INT,
    montantItv DECIMAL(18, 2),
    numeroModification INT,
    dateSoumission DATETIME NOT NULL DEFAULT GETDATE (),
    CONSTRAINT PK_ors_soumis_a_validation PRIMARY KEY (id)
);

CREATE TABLE type_document (
    id INT IDENTITY (1, 1),
    typeDocument VARCHAR(50),
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_type_document_dit PRIMARY KEY (id)
);

CREATE TABLE type_operation (
    id INT IDENTITY (1, 1),
    typeOperation VARCHAR(50),
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_type_operation PRIMARY KEY (id)
);

CREATE TABLE historique_operation_document (
    id INT IDENTITY (1, 1),
    idOrSoumisAValidation INT,
    numeroDocument INT,
    dateOperation DATETIME DEFAULT GETDATE (),
    utilisateur VARCHAR(50),
    idTypeOperation INT,
    idTypeDocument INT,
    pathPieceJointe VARCHAR(500),
    CONSTRAINT PK_historique_operation_document PRIMARY KEY (id),
    CONSTRAINT FK_historique_operation_document_id_or_soumis_a_validation FOREIGN KEY (idOrSoumisAValidation) REFERENCES ors_soumis_a_validation (id),
    CONSTRAINT FK_historique_operation_document_type_operation FOREIGN KEY (idTypeOperation) REFERENCES type_operation (id),
    CONSTRAINT FK_historique_operation_document_type_document FOREIGN KEY (idTypeDocument) REFERENCES type_document (id),
);