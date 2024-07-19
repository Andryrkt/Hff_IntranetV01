-- Créer la table permissions avec une contrainte de clé primaire nommée
CREATE TABLE permissions (
    id INT IDENTITY (1, 1),
    permission_name VARCHAR(255) NOT NULL,
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_permissions_id PRIMARY KEY (id)
);