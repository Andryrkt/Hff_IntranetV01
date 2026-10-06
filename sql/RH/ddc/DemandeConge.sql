CREATE TABLE demande_de_conge
(
    id INT IDENTITY(1,1) NOT NULL,
    Type_Demande CHAR(100),
    Numero_Demande CHAR(11),
    Matricule CHAR(4),
    Nom_Prenoms CHAR(100),
    Date_Demande DATE,
    Agence_Service CHAR(10),
    Adresse_Mail_Demandeur CHAR(100),
    Sous_type_decument CHAR(100),
    Duree_Conge DECIMAL(5,2),
    Date_Debut DATE,
    Date_Fin DATE,
    Solde_Conge DECIMAL(5,2),
    Motif_Conge CHAR(100),
    Statut_Demande CHAR(50),
    Date_Statut DATE,
    pdf_demande VARCHAR(MAX),
    Agence_Debiteur VARCHAR(10)
        CONSTRAINT PK_demande_de_conge_id PRIMARY KEY (id)
);