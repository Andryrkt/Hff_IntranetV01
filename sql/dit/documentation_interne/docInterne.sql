CREATE TABLE DW_Processus_procedure (
    id INT IDENTITY(1,1),
    id_document VARCHAR(11),
    id_tiroir VARCHAR(100),
    nom_document VARCHAR(100),
    processus_lie VARCHAR(50),
    type_document VARCHAR(50),
    date_document DATE,
    date_de_prochaine_revue DATE,
    nom_du_responsable VARCHAR(50),
    email_responsable_processus VARCHAR(50),
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    numero_version INT,
    code_service VARCHAR(3),
    code_agence VARCHAR(3),
    statut VARCHAR(50),
    perimetre VARCHAR(50),
    mot_cle VARCHAR(1000),
    numero_version_2 INT,
    path VARCHAR(100)
PRIMARY KEY (id)
);


CREATE TABLE mise_a_jour_processus (
    [id_update] [int] IDENTITY(1,1) NOT NULL,
	[date_update] [date] NULL,
	[heure_update] [time](7) NULL,
PRIMARY KEY (id_update));



CREATE TABLE document_a_telecharger_processus (
	[id_update] [int] NULL,
	[id_doc] [int] NULL,
	[id_tiroir] [varchar](100) NULL,
	[operation] [varchar](50) NULL
) ON [PRIMARY]
GO



ALTER TABLE mise_a_jour_processus ADD  DEFAULT (getdate()) FOR [date_update]
GO

ALTER TABLE mise_a_jour_processus ADD  DEFAULT (getdate()) FOR [heure_update]
GO


