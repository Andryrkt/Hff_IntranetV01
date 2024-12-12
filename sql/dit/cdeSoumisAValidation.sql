CREATE TABLE cde_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numero_cde INT,
    date_soumission DATETIME2,
    utilisateur VARCHAR(255),
    numero_version INT,
    numero_or TEXT,
    statut VARCHAR(255),
    CONSTRAINT PK_cde_soumis_a_validation PRIMARY KEY (id)
);