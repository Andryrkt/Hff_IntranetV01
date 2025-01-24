CREATE TABLE Log_utilisateur (
	id INT IDENTITY (1, 1),
    utilisateur VARCHAR(255) NOT NULL,
    nom_page VARCHAR(255) NOT NULL,
    date_heure_consultation DATETIME2(0) NOT NULL,
    params VARCHAR(MAX) NULL,
    id_utilisateur INT,
    id_page INT
);

ALTER TABLE Log_utilisateur
ADD CONSTRAINT FK_Log_users FOREIGN KEY (id_utilisateur)
REFERENCES users(id);

ALTER TABLE Log_utilisateur
ADD CONSTRAINT FK_Log_pages FOREIGN KEY (id_page)
REFERENCES Hff_pages(id);

ALTER TABLE Log_utilisateur
ADD machine_utilisateur VARCHAR(255) NOT NULL;

