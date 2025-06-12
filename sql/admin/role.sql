-- Créer la table roles avec une contrainte de clé primaire nommée
CREATE TABLE roles (
    id INT IDENTITY (1, 1),
    role_name VARCHAR(255) NOT NULL,
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_roles_id PRIMARY KEY (id)
);

-- Créer la table de liaison role_permissions avec une contrainte de clé primaire et des clés étrangères nommées
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    CONSTRAINT PK_role_permissions PRIMARY KEY (role_id, permission_id),
    CONSTRAINT FK_role_permissions_role_id FOREIGN KEY (role_id) REFERENCES roles (id),
    CONSTRAINT FK_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES permissions (id)
);