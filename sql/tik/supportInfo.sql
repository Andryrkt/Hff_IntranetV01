CREATE TABLE Demande_Support_Informatique(
	ID_Demande_Support_Informatique int IDENTITY(1,1) NOT NULL,
	date_creation datetime2(7) NULL,
	Numero_Ticket varchar(11) NOT NULL,
	Utilisateur_Demandeur varchar(50) NOT NULL,
	Mail_Demandeur varchar(50) NOT NULL,
	Mail_En_Copie varchar(1000) NULL,
	Code_Societe varchar(2) NULL,
	ID_TKI_Categorie int NULL,
	ID_TKL_Sous_Categorie int NULL,
	ID_TKL_Autres_Categorie int NULL,
	AgenceService_Emetteur varchar(5) NOT NULL,
	AgenceService_Debiteur varchar(5) NOT NULL,
	Nom_Intervenant varchar(100) NULL,
	Mail_Intervenant varchar(100) NULL,
	Objet_Demande varchar(100) NOT NULL,
	Detail_Demande varchar(5000) NOT NULL,
	Piece_Jointe1 varchar(200) NULL,
	Piece_Jointe2 varchar(200) NULL,
	Piece_Jointe3 varchar(200) NULL,
	Date_Deb_Planning date NULL,
	Date_Fin_Planning date NULL,
	ID_Projet_Informatique int NULL,
	ID_Niveau_Urgence int NULL,
	Parc_Informatique varchar(50) NOT NULL,
	Date_Fin_Souhaitee date NULL,
	heure_creation varchar(5) NULL,
	user_id int NULL,
	agence_emetteur_id int NULL,
	service_emetteur_id int NULL,
	agence_debiteur_id int NULL,
	service_debiteur_id int NULL,
	date_modification datetime2(7) NULL,
	ID_Statut_Demande int NULL,
	file_names text NULL
CONSTRAINT PRIMARY KEY (ID_Demande_Support_Informatique));

ALTER TABLE Demande_Support_Informatique ADD Date_Fin_Souhaitee DATE

ALTER TABLE Demande_Support_Informatique
ADD heure_creation VARCHAR(5)

ALTER TABLE Demande_Support_Informatique ADD user_id INT

ALTER TABLE Demande_Support_Informatique
ADD agence_emetteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD service_emetteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD agence_debiteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD service_debiteur_id INT

EXEC sp_rename 'Demande_Support_Informatique.Date_Creation',
'date_creation',
'COLUMN';

ALTER TABLE Demande_Support_Informatique
ADD date_modification DATE
ALTER TABLE Demande_Support_Informatique
ALTER COLUMN date_creation DATE

UPDATE applications SET derniere_id = 'TIK24110000' where id = 7

ALTER TABLE Demande_Support_Informatique
ALTER COLUMN date_creation DATETIME2
ALTER TABLE Demande_Support_Informatique
ALTER COLUMN date_modification DATETIME2

ALTER TABLE Demande_Support_Informatique
ADD ID_Intervenant INT;

ALTER TABLE Demande_Support_Informatique
ADD CONSTRAINT FK_User_Intervenant
FOREIGN KEY (ID_Intervenant) REFERENCES users (id);
