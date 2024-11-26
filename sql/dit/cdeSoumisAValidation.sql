CREATE TABLE cde_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numero_soumission INT,
    date_creation DATETIME2,
    date_modification DATETIME2,
    CONSTRAINT PK_cde_soumis_a_validation PRIMARY KEY (id)
);