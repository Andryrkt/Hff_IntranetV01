CREATE TABLE da_afficher
(
    id int IDENTITY(1,1),
    numero_demande_appro varchar(11),
    numero_demande_dit varchar(11),
    numero_or varchar(11),
    numero_cde varchar(11),
    statut_dal varchar(50),
    statut_or varchar(50),
    statut_cde varchar(50),
    objet_dal varchar(100),
    detail_dal varchar(1000),
    num_ligne int,
    num_ligne_tableau int,
    qte_dem int,
    qte_dispo int,
    qte_en_attent int,
    qte_livrer int,
    art_constp varchar(3),
    art_refp varchar(50),
    art_desi varchar(100),
    code_fams1 varchar(10),
    art_fams1 varchar(50),
    code_fams2 varchar(50),
    art_fams2 varchar(50),
    numero_fournisseur varchar(7),
    nom_fournisseur varchar(50),
    date_fin_souhaitee_l DATETIME2(0),
    commentaire varchar(1000),
    prix_unitaire VARCHAR(100),
    total VARCHAR(100),
    est_fiche_technique BIT NOT NULL DEFAULT 0,
    nom_fiche_technique VARCHAR(255),
    pj_fiche_technique VARCHAR(255),
    pj_new_ate text,
    pj_proposition_appro text,
    pj_bc text,
    catalogue BIT NOT NULL DEFAULT 0,
    date_livraison_prevue DATETIME2(0),
    valide_par VARCHAR(50),
    numero_version INT DEFAULT 0,
    niveau_urgence VARCHAR(5),
    jours_dispo int,
    demandeur varchar(100),
    id_da INT,
    achat_direct BIT NOT NULL DEFAULT 0,
    position_bc varchar(10),
    date_planning_or DATETIME2(0),
    or_a_resoumettre BIT NOT NULL DEFAULT 0,
    numero_ligne_ips INT,
    date_demande DATETIME2(0),
    est_dalr BIT NOT NULL DEFAULT 0,
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),
    CONSTRAINT PK_da_afficher PRIMARY KEY (id)
);

ALTER TABLE da_afficher ADD bc_envoyer_fournisseur BIT NOT NULL DEFAULT 0;
ALTER TABLE da_afficher ADD agence_emmetteur_id int;
ALTER TABLE da_afficher ADD Service_emmetteur_id int;
ALTER TABLE da_afficher ADD agence_debiteur_id int;
ALTER TABLE da_afficher ADD service_debiteur_id int;

ALTER TABLE da_afficher ADD demande_appro_id INT NOT NULL;
ALTER TABLE da_afficher ADD demande_appro_l_id INT DEFAULT NULL;
ALTER TABLE da_afficher ADD demande_appro_lr_id INT DEFAULT NULL;
ALTER TABLE da_afficher ADD dit_id INT DEFAULT NULL;

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_demande_appro FOREIGN KEY (demande_appro_id) REFERENCES demande_appro (id);

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_demande_appro_l FOREIGN KEY (demande_appro_l_id) REFERENCES demande_appro_l (id);

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_demande_appro_l_r FOREIGN KEY (demande_appro_lr_id) REFERENCES demande_appro_l_r (id);

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_dit FOREIGN KEY (dit_id) REFERENCES demande_intervention (id);


