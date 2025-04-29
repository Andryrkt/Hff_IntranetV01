
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
)


ALTER TABLE Demande_Appro 
ADD id_Materiel INT

ALTER TABLE Demande_Appro 
ADD statut_email VARCHAR(100)
