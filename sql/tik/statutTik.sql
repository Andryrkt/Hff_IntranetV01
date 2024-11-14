
CREATE TABLE TKI_Statut_Ticket_Informatique (
	ID_TKI_Statut int IDENTITY(1,1) NOT NULL,
	Numero_Ticket varchar(11) NOT NULL,
	Code_Statut varchar(3) NOT NULL,
	Date_Statut datetime2(7) NULL
CONSTRAINT PRIMARY KEY (ID_TKI_Statut));