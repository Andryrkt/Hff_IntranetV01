CREATE TABLE ri_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numero_dit VARCHAR(11),
    numero_oR VARCHAR(8),
    date_soumission DATE,
    numero_soumission INT,
    statut VARCHAR(50),
    heureSoumission VARCHAR(5),
    CONSTRAINT PK_ri_soumis_a_validation PRIMARY KEY (id)
);