CREATE TABLE TKI_Commentaires(
	id int IDENTITY(1,1) NOT NULL,
	numeroTicket nvarchar(11) NOT NULL,
	nomUtilisateur nvarchar(50) NOT NULL,
	commentaires varchar(max) NOT NULL,
	piecesJointes1 nvarchar(100) NULL,
	piecesJointes2 nvarchar(100) NULL,
	piecesJointes3 nvarchar(100) NULL,
	dateCommentaire datetime2(6) NOT NULL
CONSTRAINT PRIMARY KEY (id));
