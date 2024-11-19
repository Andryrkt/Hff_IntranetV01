CREATE TABLE Agence_service_autorise(
	id int IDENTITY(1,1) NOT NULL,
	Session_Utilisateur varchar(50) NOT NULL,
	Code_AgenceService_IRIUM varchar(5) NULL,
	Date_creation date NOT NULL
CONSTRAINT PRIMARY KEY (id));
