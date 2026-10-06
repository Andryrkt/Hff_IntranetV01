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
	part_day_planning varchar(2) NULL,
	Date_Deb_Planning datetime2(0) NULL,
	Date_Fin_Planning datetime2(0) NULL,
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
	commentaire text NULL,
	file_names text NULL,
	ID_Intervenant INT,
	ID_Validateur INT
);



UPDATE applications SET derniere_id = 'TIK24110000' where id = 7



ALTER TABLE Demande_Support_Informatique
ADD CONSTRAINT FK_User_Intervenant
FOREIGN KEY (ID_Intervenant) REFERENCES users (id);


ALTER TABLE Demande_Support_Informatique
ADD CONSTRAINT FK_User_Validateur
FOREIGN KEY (ID_Validateur) REFERENCES users (id);
