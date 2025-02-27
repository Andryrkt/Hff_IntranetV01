--crée la table application
CREATE TABLE applications (
    id INT IDENTITY (1, 1),
    nom VARCHAR(255) NOT NULL,
    code_app VARCHAR(3) NOT NULL,
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_applications_id PRIMARY KEY (id)
)