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
        WHEN LEFT(agence_service_emmeteur, 2) = '50' THEN '6'
        WHEN LEFT(agence_service_emmeteur, 2) = '90' THEN '9'
        WHEN LEFT(agence_service_emmeteur, 2) = '01' THEN '1'
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
        WHEN RIGHT(agence_service_emmeteur, 3) = 'COM' THEN '2'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAS' THEN '36'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LR6' THEN '41'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ATE' THEN '3'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LCD' THEN '9'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'UMP' THEN '17'
        ELSE '0'
    END;

UPDATE demande_intervention
SET
    agence_debiteur_id = CASE
        WHEN LEFT(agence_service_debiteur, 2) = '92' THEN '11'
        WHEN LEFT(agence_service_debiteur, 2) = '80' THEN '8'
        WHEN LEFT(agence_service_debiteur, 2) = '91' THEN '10'
        WHEN LEFT(agence_service_debiteur, 2) = '90' THEN '9'
        WHEN LEFT(agence_service_debiteur, 2) = '01' THEN '1'
        WHEN LEFT(agence_service_debiteur, 2) = '50' THEN '6'
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
        WHEN RIGHT(agence_service_debiteur, 3) = 'AMB' THEN '30'
        WHEN RIGHT(agence_service_debiteur, 3) = 'TSI' THEN '23'
        WHEN RIGHT(agence_service_debiteur, 3) = 'LGR' THEN '46'
        WHEN RIGHT(agence_service_debiteur, 3) = 'LR6' THEN '41'
        WHEN RIGHT(agence_service_debiteur, 3) = 'TUL' THEN '29'
        WHEN RIGHT(agence_service_debiteur, 3) = 'LST' THEN '42'
        WHEN RIGHT(agence_service_debiteur, 3) = 'LCD' THEN '9'
        WHEN RIGHT(agence_service_debiteur, 3) = 'INF' THEN '13'
        WHEN RIGHT(agence_service_debiteur, 3) = 'COM' THEN '2'
        WHEN RIGHT(agence_service_debiteur, 3) = 'MAS' THEN '36'
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

ALTER TABLE demande_intervention ADD section_support_1 VARCHAR(255)

ALTER TABLE demande_intervention ADD section_support_2 VARCHAR(255)

ALTER TABLE demande_intervention ADD section_support_3 VARCHAR(255);

-- à revoire
select
    slor_nogrp / 100 as numItv,
    (
        select count(*)
        from sav_itv
        where
            sitv_numor = '16412642'
    ) as nombreLigneitv,
    sum(
        CASE
            WHEN slor_typlig = 'P' THEN (
                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
            )
            WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
        END * slor_pxnreel
    ) as montantItv,
    (
        select SUM(
                CASE
                    WHEN slor_typlig = 'P' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                END * slor_pxnreel
            )
        from sav_lor
        where
            slor_typlig = 'P'
            AND slor_numor = '16412642'
    ) as montantPiece,
    (
        select SUM(
                CASE
                    WHEN slor_typlig = 'P' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                END * slor_pxnreel
            )
        from sav_lor
        where
            slor_constp = 'ZST'
            AND slor_numor = '16412642'
    ) as montantAchatLocaux,
    (
        select SUM(
                CASE
                    WHEN slor_typlig = 'P' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                END * slor_pxnreel
            )
        from sav_lor
        where
            slor_constp = 'LUB'
            AND slor_numor = '16412642'
    ) as montantLubrifiants
from sav_lor
WHERE
    slor_numor = '16412642'
GROUP BY
    1

UPDATE demande_intervention
SET
    section_affectee = CASE
        WHEN section_affectee = 'ELECTRICITE' THEN 'Chef section Électricité'
        WHEN section_affectee = 'MOTEURS ET MACHINES OUTILS' THEN 'Chef section moteurs et machines outils'
        WHEN section_affectee = 'TOLERIE & PEINTURE & MECANIQUE' THEN 'Chef section tolerie & peinture & mecanique'
        WHEN section_affectee = 'FER ET BATIMENTS' THEN 'Chef section Fer et Bâtiment'
        WHEN section_affectee = 'CUSTOMER SUPPORT' THEN 'Chef de section Customer support'
        WHEN section_affectee = 'FROID' THEN 'Chef section froid'
        ELSE section_affectee
    END;

--Ajout de colone eta_facturation
ALTER TABLE demande_intervention
ADD etat_facturation VARCHAR(255)
--Ajout de colone ri
ALTER TABLE demande_intervention
ADD ri VARCHAR(255)

UPDATE wor_niveau_urgence
SET
    description = CASE
        WHEN description = 'CRITIQUE' THEN 'P0'
        WHEN description = 'URGENT' THEN 'P1'
        WHEN description = 'NORMAL' THEN 'P2'
        ELSE description
    END;

INSERT INTO
    wor_niveau_urgence (
        description,
        date_creation,
        date_modification
    )
VALUES (
        'P3',
        '2024-11-04',
        '2024-11-04'
    );

ALTER TABLE demande_intervention
ADD mail_client VARCHAR(100)