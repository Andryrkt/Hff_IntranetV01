CREATE TABLE ors_soumis_a_validation
(
    id INT IDENTITY (1, 1),
    numeroOR VARCHAR(8),
    numeroItv INT,
    nombreLigneItv INT,
    montantItv DECIMAL(18, 2),
    numeroVersion INT,
    montantPiece DECIMAL(18, 2),
    montantMo DECIMAL(18, 2),
    montantAchatLocaux DECIMAL(18, 2),
    montantFraisDivers DECIMAL(18, 2),
    montantLubrifiants DECIMAL(18, 2),
    libellelItv VARCHAR(500),
    dateSoumission DATE,
    heureSoumission VARCHAR(5) CONSTRAINT PK_ors_soumis_a_validation PRIMARY KEY (id)
);

ALTER TABLE ors_soumis_a_validation
ADD numeroDIT VARCHAR(11);

-- remplir la colonne numeroDIT avec les données de la table demande_intervention
UPDATE ors_soumis_a_validation
SET numeroDIT = di.numero_demande_dit
FROM ors_soumis_a_validation
    JOIN demande_intervention AS di
    ON ors_soumis_a_validation.numeroOR = di.numero_or AND num_migr = 7;

CREATE TABLE type_document
(
    id INT IDENTITY (1, 1),
    typeDocument VARCHAR(50),
    date_creation DATE,
    date_modification DATE,
    CONSTRAINT PK_type_document_dit PRIMARY KEY (id)
);

ALTER TABLE type_document ADD libelle_document VARCHAR(255)

CREATE TABLE type_operation
(
    id INT IDENTITY (1, 1),
    typeOperation VARCHAR(50),
    date_creation DATE,
    date_modification DATE,
    CONSTRAINT PK_type_operation PRIMARY KEY (id)
);

CREATE TABLE historique_operation_document
(
    id INT IDENTITY (1, 1),
    numeroDocument VARCHAR(50),
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

select
    slor_numor,
    sitv_datdeb,
    trim(seor_refdem) NUMERo_DIT,
    sitv_interv NUMERO_ITV,
    sitv_comment LIBELLE_ITV,
    count(slor_constp) NOMBRE_LIGNE,
    Sum(
        CASE
            WHEN slor_typlig = 'P' THEN (
                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
            )
            WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
        END * CASE
            WHEN slor_typlig = 'P' THEN slor_pxnreel
            WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
        END
    ) MONTANT_ITV,

    Sum(
    CASE
        WHEN slor_typlig = 'P'
        AND slor_constp NOT like 'Z%'
        AND slor_constp <> 'LUB' THEN (
            nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0)
        )
    END * CASE
        WHEN slor_typlig = 'P' THEN slor_pxnreel
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
    END
) AS MONTANT_PIECE,

    Sum(
    CASE
        WHEN slor_typlig = 'M' THEN slor_qterea
    END * CASE
        WHEN slor_typlig = 'P' THEN slor_pxnreel
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
    END
) AS MONTANT_MO,

    Sum(
    CASE
        WHEN slor_constp = 'ZST' THEN (
            slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
        )
    END * CASE
        WHEN slor_typlig = 'P' THEN slor_pxnreel
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
    END
) AS MONTANT_ACHATS_LOCAUX,

    Sum(
    CASE
        WHEN slor_constp <> 'ZST'
        AND slor_constp like 'Z%' THEN slor_qterea
    END * CASE
        WHEN slor_typlig = 'P' THEN slor_pxnreel
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
    END
) AS MONTANT_DIVERS,

    Sum(
    CASE
        WHEN slor_typlig = 'P'
        AND slor_constp NOT like 'Z%'
        AND slor_constp = 'LUB' THEN (
            nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0)
        )
    END * CASE
        WHEN slor_typlig = 'P' THEN slor_pxnreel
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
    END
) AS MONTANT_LUBRIFIANTS

from sav_eor, sav_lor, sav_itv
WHERE
    seor_numor = slor_numor
    AND seor_serv <> 'DEV'
    AND sitv_numor = slor_numor
    AND sitv_interv = slor_nogrp / 100

    AND sitv_pos NOT IN('FC', 'FE', 'CP', 'ST')
--AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS')
--AND seor_numor IN (16406341,16406354)
--AND SEOR_SUCC = '01'
group by
    1,
    2,
    3,
    4,
    5
order by slor_numor, sitv_interv

ALTER TABLE type_operation ALTER COLUMN date_creation DATE;

ALTER TABLE type_operation ALTER COLUMN date_modification DATE;

ALTER TABLE type_operation
ADD heure_creation TIME,
heure_modification TIME;

INSERT INTO
    type_operation
    (
    typeOperation,
    date_creation,
    heure_creation,
    date_modification,
    heure_modification
    )
VALUES
    (
        'SOUMISSION',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'VALIDATION',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'MODIFICATION',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'SUPPRESSION',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'CREATION',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'CLOTURE',
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    );

ALTER TABLE historique_operation_document
ALTER COLUMN dateOperation DATE;

ALTER TABLE historique_operation_document
DROP CONSTRAINT DF__historiqu__dateO__345EC57D;

ALTER TABLE historique_operation_document ADD heure_operation TIME

CREATE TABLE or_soumis_validation_historique
(
    orsSoumisAValidation_id INT,
    historique_Operation_Doc_id INT,
    CONSTRAINT PK_or_soumis_validation_historique PRIMARY KEY (
        orsSoumisAValidation_id,
        historique_Operation_Doc_id
    ),
    CONSTRAINT FK_or_soumis_validation_historique_orsSoumisAValidation_id FOREIGN KEY (orsSoumisAValidation_id) REFERENCES ors_soumis_a_validation (id),
    CONSTRAINT FK_or_soumis_validation_historique_historique_Operation_Doc_id FOREIGN KEY (historique_Operation_Doc_id) REFERENCES historique_operation_document (id)
);

ALTER TABLE historique_operation_document
ADD statutOperation VARCHAR(255) NULL;

ALTER TABLE historique_operation_document
ADD libelleOperation VARCHAR(255) NULL;

ALTER TABLE ors_soumis_a_validation
ADD observation VARCHAR(3000) NULL;
