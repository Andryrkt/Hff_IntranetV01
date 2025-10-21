
CREATE TABLE Demande_Appro
(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) NOT NULL,
    demandeur varchar(100) not null,
    achat_direct BIT,
    devis_achat BIT,
    numero_demande_dit varchar(11) not null,
    objet_dal varchar(100) not null,
    detail_dal varchar(1000) null,
    agence_emmetteur_id int Not null,
    Service_emmetteur_id int Not null,
    agence_service_emmeteur varchar(6) not null,
    agence_debiteur_id int not null,
    service_debiteur_id int not null,
    agence_service_debiteur varchar(6) not null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    date_heure_fin_souhaitee DATETIME2(0) null,
    statut_dal varchar(100) null,
    CONSTRAINT PK_Demande_Appro PRIMARY KEY (id)
);


ALTER TABLE Demande_Appro 
ADD id_Materiel INT;

ALTER TABLE Demande_Appro 
ADD statut_email VARCHAR(100);

ALTER TABLE Demande_Appro
ADD est_validee bit DEFAULT 0;

ALTER TABLE Demande_Appro
ADD valide_par VARCHAR(50);

ALTER TABLE Demande_Appro
ADD nom_fichier_reference_zst VARCHAR(255);

ALTER TABLE Demande_Appro ADD user_id INT;

ALTER TABLE Demande_Appro
ADD CONSTRAINT FK_User_Id
FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE Demande_Appro ADD validateur_id INT;

ALTER TABLE Demande_Appro
ADD CONSTRAINT FK_Validateur_id
FOREIGN KEY (validateur_id) REFERENCES users (id);

alter TABLE Demande_Appro ADD niveau_urgence VARCHAR(50);

ALTER TABLE Demande_Appro
ADD Devis_demander bit null;

ALTER TABLE Demande_Appro
ADD Date_demande_devis DATETIME2(0) null;

ALTER TABLE Demande_Appro
ADD Devis_demander_par varchar(100) null;