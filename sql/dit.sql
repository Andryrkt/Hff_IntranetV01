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