CREATE TABLE DW_Processus_procedure (
    id INT IDENTITY(1,1),
    id_document VARCHAR(11),
    nom_document VARCHAR(100),
    processus_lie VARCHAR(50),
    type_document VARCHAR(50),
    date_document DATE,
    date_de_prochaine_revue DATE,
    nom_du_responsable VARCHAR(50),
    email_responsable_processus VARCHAR(50),
    derniere_modification DATE,
    numero_version INT,
    code_service VARCHAR(3),
    code_agence VARCHAR(2),
    statut VARCHAR(50),
    perimetre VARCHAR(50),
    mot_cle VARCHAR(1000),
    numero_version_2 INT,
    path VARCHAR(100)
PRIMARY KEY (id)
);