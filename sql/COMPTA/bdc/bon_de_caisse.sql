CREATE TABLE Demande_de_bon_de_caisse
(
    id INT IDENTITY(1,1) NOT NULL,
    Type_Demande CHAR(100),
    Numero_Demande CHAR(11),
    Date_Demande DATE,
    Caisse_Retrait CHAR(30),
    Type_Paiement CHAR(30),
    Agence_Debiteur CHAR(10),
    Service_Debiteur CHAR(10),
    Retrait_Lie CHAR(50),
    Matricule CHAR(4),
    Adresse_Mail_Demandeur CHAR(100),
    Motif_Demande CHAR(300),
    Montant_Payer DECIMAL(15,2),
    Devise CHAR(3),
    Statut_Demande CHAR(50),
    Date_Statut DATE,
    CONSTRAINT PK_bon_de_caisse_id PRIMARY KEY (id)
);

ALTER TABLE Demande_de_bon_de_caisse ADD pdf_demande VARCHAR(MAX) NULL;
ALTER TABLE Demande_de_bon_de_caisse ADD agence_emetteur VARCHAR(2) NULL;
ALTER TABLE Demande_de_bon_de_caisse ADD service_emetteur VARCHAR(3) NULL;
ALTER TABLE Demande_de_bon_de_caisse ADD nom_validateur_final VARCHAR(MAX) NULL;
