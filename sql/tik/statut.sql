ALTER TABLE TKI_Statut_Ticket_Informatique
ALTER COLUMN Date_Statut DATETIME2

CREATE TABLE STATUT_DEMANDE(
	ID_Statut_Demande int IDENTITY(1,1) NOT NULL,
	Code_Application varchar(3) NOT NULL,
	Code_Statut varchar(3) NULL,
	Description nvarchar(100) NULL,
	Date_creation date NOT NULL,
	date_modification date NULL
)
