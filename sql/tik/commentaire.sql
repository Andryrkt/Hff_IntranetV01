

CREATE TABLE TkiCommentaires (
	id int IDENTITY(1,1) NOT NULL,
	numeroTicket nvarchar(11) NOT NULL,
	nomUtilisateur nvarchar(50) NOT NULL,
	commentaires varchar(max) NOT NULL,
	piecesJointes1 nvarchar(100) NULL,
	piecesJointes2 nvarchar(100) NULL,
	piecesJointes3 nvarchar(100) NULL,
	date_creation datetime2(6) NOT NULL,
	date_modification datetime2(7) NULL,
	id_demande_support int NULL,
	id_utilisateur int NULL,
	fichiers_detail varchar(max) NULL

	CONSTRAINT PRIMARY KEY (id) 
	CONSTRAINT FK_Demande_Support_Informatique FOREIGN KEY(id_demande_support) REFERENCES Demande_Support_Informatique (ID_Demande_Support_Informatique)
   	CONSTRAINT FK_User_Commentaire FOREIGN KEY(id_utilisateur) REFERENCES users (id)
);

ALTER TABLE Demande_Support_Informatique
ADD CONSTRAINT FK_User_Intervenant
FOREIGN KEY (ID_Intervenant) REFERENCES users (id);
