CREATE TABLE da_valider
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
    qte_dem int,
    qte_dispo int,
    qte_a_livrer int,
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
    est_fiche_technique BIT DEFAULT 0,
    pj_fiche_technique VARCHAR(255),
    pj_new_ate text,
    pj_proposition_appro text,
    pj_bc text,
    catalogue BIT,
    date_livraison_prevue DATETIME2(0),
    valide_par VARCHAR(50),
    numero_version INT DEFAULT 0,
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),
    CONSTRAINT PK_da_valider PRIMARY KEY (id)
);

alter TABLE da_valider ADD niveau_urgence VARCHAR(50);
alter TABLE da_valider ADD nom_fiche_technique VARCHAR(255);

ALTER TABLE da_valider ADD jours_dispo int;

ALTER TABLE da_valider ADD qte_en_attent int;

ALTER TABLE da_valider ADD demandeur varchar(100);

ALTER TABLE da_valider ADD id_da INT;

ALTER TABLE da_valider ADD achat_direct BIT NOT NULL DEFAULT 0 WITH VALUES;

ALTER TABLE da_valider ADD position_bc varchar(10);


ALTER TABLE da_valider ADD date_planning_or DATETIME2(0);

ALTER TABLE da_valider ADD or_a_resoumettre BIT DEFAULT 0;
