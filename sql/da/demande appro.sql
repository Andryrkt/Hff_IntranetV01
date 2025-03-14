
CREATE TABLE Demande_Appro(
    id int IDENTITY(1,1) NOT NULL,
    numero_demande_appro varchar(11) NOT NULL,
    achat_direct BIT,
    devis_achat	BIT,
    numero_demande_dit	varchar(11)	not null,
    objet_dal varchar(100)	not null,
    detail_dal	varchar(1000) null,
    agence_emmeteur_id	int(11)	Not null,
    Service_emmetteur_id	int(11)	Not null,
    agence_service_emmeteur	varchar(6)	not null,
    agence_debiteur_id	int(11)	not null,
    service_debiteur_id	int(11)	not null,
    agence_service_debiteur	varchar(6)	not null,
    date_heure_creation	datetime not null,
    date_heure_fin_souhaitee datetime null,
    statut_dal	varchar(100) null,
    CONSTRAINT PK_Demande_Appro PRIMARY KEY (id)
)
