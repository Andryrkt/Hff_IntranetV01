
CREATE TABLE Projet_Informatique(
	ID_Projet_Informatique int IDENTITY(1,1) NOT NULL,
	Date_Creation datetime NOT NULL,
	Numero_Projet varchar(11) NOT NULL,
	Utilisateur_Demandeur varchar(50) NOT NULL,
	Mail_Demandeur varchar(50) NOT NULL,
	Code_Societe varchar(2) NOT NULL,
	ID_TKI_Categorie int NULL,
	ID_TKI_Sous_Categorie int NULL,
	AgenceService_Emetteur varchar(5) NOT NULL,
	AgenceService_Debiteur varchar(5) NOT NULL,
	Nom_Intervenant varchar(100) NOT NULL,
	Mail_Intervenant varchar(100) NOT NULL,
	Objet_Demande varchar(100) NOT NULL,
	Detail_Demande varchar(500) NOT NULL,
	Piece_Jointe1 varchar(200) NULL,
	Piece_Jointe2 varchar(200) NULL,
	Piece_Jointe3 varchar(200) NULL,
	Date_Deb_Planning date NULL,
	Date_Fin_Planning date NULL,
	Avancement_Projet varchar(50) NULL
CONSTRAINT PRIMARY KEY (ID_Projet_Informatique));
