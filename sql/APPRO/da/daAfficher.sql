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


ALTER TABLE da_afficher DROP COLUMN id_da;

ALTER TABLE da_afficher ADD 
bc_envoyer_fournisseur BIT NOT NULL DEFAULT 0,
agence_emmetteur_id int,
Service_emmetteur_id int,
agence_debiteur_id int,
service_debiteur_id int,
demande_appro_id INT NOT NULL,
dit_id INT DEFAULT NULL,
deleted_by varchar(100),
deleted BIT NOT NULL DEFAULT 0
;
ALTER TABLE da_afficher ADD 
date_creation_bc DATETIME2(0),
date_validation_bc DATETIME2(0),
date_reception_article DATETIME2(0)
date_livraison_article DATETIME2(0)
;

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_demande_appro FOREIGN KEY (demande_appro_id) REFERENCES demande_appro (id);

ALTER TABLE da_afficher
    ADD CONSTRAINT FK_da_dit FOREIGN KEY (dit_id) REFERENCES demande_intervention (id);

-- permet de remplir la table da_afficher avec les données de da_valider
INSERT INTO da_afficher
    (
    numero_demande_appro, numero_demande_dit, numero_or, numero_cde,
    statut_dal, statut_or, statut_cde, objet_dal, detail_dal,
    num_ligne, qte_dem, qte_dispo, qte_en_attent, qte_livrer,
    art_constp, art_refp, art_desi, code_fams1, art_fams1,
    code_fams2, art_fams2, numero_fournisseur, nom_fournisseur,
    date_fin_souhaitee_l, commentaire, prix_unitaire, total,
    est_fiche_technique, nom_fiche_technique, pj_fiche_technique, pj_new_ate,
    pj_proposition_appro, pj_bc, catalogue, date_livraison_prevue,
    valide_par, numero_version, niveau_urgence, jours_dispo, demandeur,
    id_da, achat_direct, position_bc, date_planning_or,
    or_a_resoumettre, numero_ligne_ips, date_creation, date_modification
    )
SELECT
    numero_demande_appro, numero_demande_dit, numero_or, numero_cde,
    statut_dal, statut_or, statut_cde, objet_dal, detail_dal,
    num_ligne, qte_dem, qte_dispo, qte_en_attent, qte_livrer,
    art_constp, art_refp, art_desi, code_fams1, art_fams1,
    code_fams2, art_fams2, numero_fournisseur, nom_fournisseur,
    date_fin_souhaitee_l, commentaire, prix_unitaire, total,
    est_fiche_technique, nom_fiche_technique, pj_fiche_technique, pj_new_ate,
    pj_proposition_appro, pj_bc, catalogue, date_livraison_prevue,
    valide_par, numero_version, niveau_urgence, jours_dispo, demandeur,
    id_da, achat_direct, position_bc, date_planning_or,
    or_a_resoumettre, numero_ligne_ips, date_creation, date_modification
FROM da_valider;

update da_afficher set qte_dispo=0 where qte_dispo is NULL

update da_afficher set qte_en_attent=0 where qte_en_attent  is NULL

--? IMPORTANT requête de mise à jour en masse ?--

UPDATE da
SET 
da.agence_emmetteur_id = d.agence_emmetteur_id,
da.Service_emmetteur_id = d.Service_emmetteur_id,
da.agence_debiteur_id = d.agence_debiteur_id,
da.service_debiteur_id = d.service_debiteur_id
FROM da_afficher da
JOIN Demande_Appro d ON da.numero_demande_appro = d.numero_demande_appro ;

ALTER TABLE da_afficher ADD numero_version_or_maj_statut INT NULL;
ALTER TABLE da_afficher ADD date_derniere_bav DATETIME2(0) null;
ALTER TABLE da_afficher ADD date_maj_statut_or DATETIME2(0) null;
ALTER TABLE da_afficher ADD est_facture_bl_soumis bit DEFAULT 0;
ALTER TABLE da_afficher ADD numero_intervention_ips INT;
ALTER TABLE da_afficher ADD mail_envoye bit DEFAULT 0;
ALTER TABLE da_afficher ADD non_dispo bit DEFAULT 0;
ALTER TABLE da_afficher ADD est_bl_reappro_soumis bit DEFAULT 0;
ALTER TABLE da_afficher ADD est_correction_statut_bc bit DEFAULT 0;