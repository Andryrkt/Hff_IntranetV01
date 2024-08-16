-- Créer la table permissions avec une contrainte de clé primaire nommée
CREATE TABLE permissions (
    id INT IDENTITY (1, 1),
    permission_name VARCHAR(255) NOT NULL,
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_permissions_id PRIMARY KEY (id)
);

/** TABLE RELATION ENTRE L4UTILISATEUR ET LE PERMISSION */
CREATE TABLE users_permission (
    user_id INT,
    permission_id INT,
    CONSTRAINT PK_users_permission PRIMARY KEY (user_id, permission_id),
    CONSTRAINT FK_users_permission_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_permission_permission_id FOREIGN KEY (permission_id) REFERENCES permissions (id)
);