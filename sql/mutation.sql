CREATE TABLE Demande_de_mutation(
    id int IDENTITY(1,1) NOT NULL,
	Numero_Mutation varchar(11) NOT NULL,
	Date_Demande date NOT NULL,
	Nom varchar(100) NULL,
	Prenom varchar(100) NULL,
	Matricule varchar(50) NULL,
	Type_Document varchar(10) NOT NULL,
	Code_Agence_Service_Debiteur varchar(6) NULL,
	LibelleCodeAgence_Service varchar(50) NULL,
	Date_Debut date NOT NULL,
	Date_Fin date NOT NULL,
	Lieu_Mutation varchar(100) NOT NULL,
	Motif_Mutation varchar(100) NOT NULL,
	Client varchar(100) NULL,
	Nombre_Jour_Avance int NULL,
	Indemnite_Forfaitaire varchar(50) NULL,
	Total_Indemnite_Forfaitaire varchar(50) NULL,
	Motif_Autres_depense_1 varchar(50) NULL,
	Autres_depense_1 varchar(50) NULL,
	Motif_Autres_depense_2 varchar(50) NULL,
	Autres_depense_2 varchar(50) NULL,
	Total_Autres_Depenses varchar(50) NULL,
	Total_General_Payer varchar(50) NULL,
	Mode_Paiement varchar(50) NULL,
	Piece_Jointe_1 varchar(50) NULL,
	Piece_Jointe_2 varchar(50) NULL,
	Utilisateur_Creation varchar(50) NOT NULL,
	Code_Statut varchar(3) NULL,
	Numero_Tel varchar(10) NULL,
	Devis varchar(3) NULL,
	statut_demande_id int NULL,
	categorie_id int NULL,
	sous_type_document_id int NULL,
	agence_emetteur_id int NULL,
	service_emetteur_id int NULL,
	agence_debiteur_id int NULL,
	service_debiteur_id int NULL,
	site_id int NULL,
    CONSTRAINT PK_Demande_de_mutation PRIMARY KEY (id)
)

INSERT INTO STATUT_DEMANDE(
	Code_Application,
	Code_Statut,
	Description,
	Date_creation,
	date_modification
) VALUES
(
	'MUT',
	'OUV',
	'A VALIDER SERVICE EMETTEUR',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'OUV',
	'A VALIDER SERVICE DESTINATAIRE',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'OUV',
	'PRE-CONTROLE ATELIER',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'OUV',
	'A VALIDER COMPTA',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'OUV',
	'A CONTROLER RH',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'ANN',
	'ANNULE CHEF DE SERVICE DESTINATAIRE',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'ANN',
	'ANNULE CHEF D''ATELIER',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'ANN',
	'ANNULE CHEF DE SERVICE EMMETTEUR',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'ANN',
	'ANNULE RH',
	GETDATE(),
	GETDATE()
),
(
	'MUT',
	'ANN',
	'ANNULE COMPTA',
	GETDATE(),
	GETDATE()
);
