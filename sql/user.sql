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