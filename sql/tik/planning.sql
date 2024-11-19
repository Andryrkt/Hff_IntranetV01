CREATE TABLE TKI_Planning (
	id int IDENTITY(1,1) NOT NULL,
	demande_id int NULL,
	dateCreation datetime2(6) NOT NULL,
	numeroTicket nvarchar(11) NOT NULL,
	datePlanning date NOT NULL,
	heureDebutPlanning nvarchar(5) NULL,
	heureFinPlanning nvarchar(5) NULL
CONSTRAINT PRIMARY KEY (id));
