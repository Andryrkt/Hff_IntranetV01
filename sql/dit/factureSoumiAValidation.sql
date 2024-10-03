CREATE TABLE facture_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numero_fact VARCHAR(8),
    numero_dit VARCHAR(11),
    numero_oR VARCHAR(8),
    date_soumission DATE,
    numero_soumission INT,
    numero_itv INT,
    montant_factureItv DECIMAL(18, 2),
    agence_debiteur VARCHAR(2),
    service_debiteur VARCHAR(50),
    statut VARCHAR(50),
    heureSoumission VARCHAR(5),
    CONSTRAINT PK_facture_soumis_a_validation PRIMARY KEY (id)
);

ALTER TABLE facture_soumis_a_validation
ADD heure_soumission VARCHAR(5)