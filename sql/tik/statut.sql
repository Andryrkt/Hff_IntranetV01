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

INSERT INTO STATUT_DEMANDE (Code_Application, Code_Statut, Description, Date_creation, date_modification)
    VALUES
        ('TKI', 'OUV', 'OUVERT',  GETDATE (),  GETDATE ()),
        ('TKI', 'REF', 'REFUDE',  GETDATE (),  GETDATE ()),
        ('TKI', 'ENC', 'ENCOURS',  GETDATE (),  GETDATE ()),
        ('TKI', 'PLA', 'PLANIFIE',  GETDATE (),  GETDATE ()),
        ('TKI', 'RES', 'RESOLU',  GETDATE (),  GETDATE ()),
        ('TKI', 'ROV', 'REOUVERT',  GETDATE (),  GETDATE ()),
        ('TKI', 'CLO', 'CLÔTURE',  GETDATE (),  GETDATE ()),
        ('TKI', 'SUS', 'SUSPENDU',  GETDATE (),  GETDATE ())
	;
