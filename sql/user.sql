ALTER TABLE users ADD fonction VARCHAR(255)

ALTER TABLE users
ADD agence_utilisateur VARCHAR(255)
ALTER TABLE users
ADD service_utilisateur VARCHAR(255)

CREATE TABLE users_agence_autoriser (
    user_id INT,
    agence_autoriser_id INT,
    CONSTRAINT PK_users_agence_autoriser PRIMARY KEY (user_id, agence_autoriser_id),
    CONSTRAINT FK_users_agence_autoriser_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_agence_autoriser_agence_autoriser_id FOREIGN KEY (agence_autoriser_id) REFERENCES agences (id)
);

CREATE TABLE agence_user (
    agence_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (agence_id, user_id),
    FOREIGN KEY (agence_id) REFERENCES agences (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

/** TABLE RELATION ENTRE L4UTILISATEUR ET LE PERMISSION */
CREATE TABLE users_permission (
    user_id INT,
    permission_id INT,
    CONSTRAINT PK_users_permission PRIMARY KEY (user_id, permission_id),
    CONSTRAINT FK_users_permission_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_permission_permission_id FOREIGN KEY (permission_id) REFERENCES permissions (id)
);

UPDATE users
SET
    users.personnel_id = (
        select id
        from Personnel
        where
            matricule = users.matricule
    )
where
    users.matricule = (
        select matricule
        from Personnel
        where
            matricule = users.matricule
    )

ALTER TABLE users ADD societe_id INT

UPDATE users
set
    societe_id = 1
    /** permet de changer le personnel_id dans la table user à partir de l'id de la table personnel */
UPDATE users
SET
    personnel_id = (
        SELECT Personnel.id
        FROM Personnel
        WHERE
            Personnel.Matricule = users.matricule
    )
WHERE
    EXISTS (
        SELECT 1
        FROM Personnel
        WHERE
            Personnel.Matricule = users.matricule
    );