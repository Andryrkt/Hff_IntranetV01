-- Remise en état du ticketing (TIK). Idempotent, à passer d'abord sur la pré-prod.
-- Après exécution : supprimer var/cache/pools (les droits des profils y sont en cache) puis se reconnecter.

SET XACT_ABORT ON;
BEGIN TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1. Colonnes vides à la création d'un ticket, mais déclarées NOT NULL
-- ---------------------------------------------------------------------------
ALTER TABLE Demande_Support_Informatique ALTER COLUMN Mail_En_Copie NVARCHAR(1000) NULL;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN Nom_Intervenant NVARCHAR(100) NULL;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN Mail_Intervenant NVARCHAR(100) NULL;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN Date_Deb_Planning DATETIME2(6) NULL;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN Date_Fin_Planning DATETIME2(6) NULL;

-- SQL Server refuse de modifier une colonne INT indexée : il faut retirer l'index puis le recréer
DROP INDEX IF EXISTS IDX_D21668BC81503EFB ON Demande_Support_Informatique;
DROP INDEX IF EXISTS IDX_D21668BCF19FE1E1 ON Demande_Support_Informatique;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN ID_TKL_Sous_Categorie INT NULL;
ALTER TABLE Demande_Support_Informatique ALTER COLUMN ID_TKL_Autres_Categorie INT NULL;
CREATE INDEX IDX_D21668BC81503EFB ON Demande_Support_Informatique (ID_TKL_Sous_Categorie);
CREATE INDEX IDX_D21668BCF19FE1E1 ON Demande_Support_Informatique (ID_TKL_Autres_Categorie);

-- Un évènement du calendrier peut ne pas être lié à un ticket
ALTER TABLE TKI_Planning ALTER COLUMN numero_ticket NVARCHAR(11) NULL;

-- ---------------------------------------------------------------------------
-- 2. Un ticket a plusieurs lignes de planning (une par jour) et plusieurs replanifications
-- ---------------------------------------------------------------------------
DROP INDEX IF EXISTS UNIQ_364899B380E95E18 ON TKI_Planning;
DROP INDEX IF EXISTS UNIQ_BA938C8880E95E18 ON TKI_Replannification;
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IDX_364899B380E95E18' AND object_id = OBJECT_ID('TKI_Planning'))
    CREATE INDEX IDX_364899B380E95E18 ON TKI_Planning (demande_id);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IDX_BA938C8880E95E18' AND object_id = OBJECT_ID('TKI_Replannification'))
    CREATE INDEX IDX_BA938C8880E95E18 ON TKI_Replannification (demande_id);

-- ---------------------------------------------------------------------------
-- 3. Faute de frappe sur le libellé du statut 59
-- ---------------------------------------------------------------------------
UPDATE Statut_demande SET Description = 'REFUSE' WHERE ID_Statut_Demande = 59 AND Code_Application = 'TKI' AND Description = 'REFUDE';

-- ---------------------------------------------------------------------------
-- 4. Routes absentes de Hff_pages : sans ligne, le contrôle d'accès renvoie 403
-- ---------------------------------------------------------------------------
INSERT INTO Hff_pages (nom, nom_route, lien, application_id, date_creation, date_modification)
SELECT p.nom, p.nom_route, p.lien, p.application_id, GETDATE(), GETDATE()
FROM (VALUES
    (N'Clôture d''un ticket',             'tik_cloture',                 '/tik-cloture/{id}',                        7),
    (N'Réouverture d''un ticket',         'tik_reouvert',                '/tik-reouvert/{id}',                       7),
    (N'Nouvelle catégorie ticket',        'tki_categorie_new',           '/admin/tki-categorie-new',                 23),
    (N'Modification catégorie ticket',    'tki_categorie_edit',          '/admin/tki-categorie-edit/{id}',           23),
    (N'Suppression catégorie ticket',     'tki_categorie_delete',        '/admin/tki-categorie-delete/{id}',         23),
    (N'Nouvelle sous-catégorie ticket',   'tki_sous_categorie_new',      '/admin/tki-sous-categorie-new',            23),
    (N'Modification sous-catégorie ticket', 'tki_sous_categorie_edit',   '/admin/tki-sous-categorie-edit/{id}',      23),
    (N'Suppression sous-catégorie ticket', 'tki_sous_categorie_delete',  '/admin/tki-sous-categorie-delete/{id}',    23),
    (N'Nouvelle autre catégorie ticket',  'tki_autres_categories_new',   '/admin/tki-autres-categories-new',         23),
    (N'Modification autre catégorie ticket', 'tki_autres_categories_edit', '/admin/tki-autres-categories-edit/{id}', 23),
    (N'Suppression autre catégorie ticket', 'tki_autres_categories_delete', '/admin/tki-autres-categories-delete/{id}', 23)
) AS p (nom, nom_route, lien, application_id)
WHERE NOT EXISTS (SELECT 1 FROM Hff_pages h WHERE h.nom_route = p.nom_route);

-- ---------------------------------------------------------------------------
-- 5. Accès au ticketing. Pilote : HFF-ADMIN (101) et service informatique (83).
--    Pour ouvrir à tous : remplacer le contenu de @profils par SELECT id FROM profil.
-- ---------------------------------------------------------------------------
DECLARE @profils TABLE (id INT);
INSERT INTO @profils (id) VALUES (101), (83);

INSERT INTO application_profil (application_id, profil_id)
SELECT 7, p.id
FROM @profils p
WHERE NOT EXISTS (SELECT 1 FROM application_profil ap WHERE ap.application_id = 7 AND ap.profil_id = p.id);

INSERT INTO application_profil_page (application_profil_id, page_id, peut_voir, peut_voir_liste_avec_debiteur, peut_multi_succursale, peut_supprimer, peut_exporter)
SELECT ap.id, h.id, 1, 0, 0, 0, 0
FROM application_profil ap
JOIN @profils p ON p.id = ap.profil_id
JOIN Hff_pages h ON h.application_id = 7 AND h.nom_route <> 'liste_tik_index_search'
WHERE ap.application_id = 7
  AND NOT EXISTS (SELECT 1 FROM application_profil_page x WHERE x.application_profil_id = ap.id AND x.page_id = h.id);

-- Gestion des catégories : HFF-ADMIN uniquement
INSERT INTO application_profil_page (application_profil_id, page_id, peut_voir, peut_voir_liste_avec_debiteur, peut_multi_succursale, peut_supprimer, peut_exporter)
SELECT ap.id, h.id, 1, 0, 0, 1, 0
FROM application_profil ap
JOIN Hff_pages h ON h.application_id = 23 AND h.nom_route LIKE 'tki[_]%'
WHERE ap.application_id = 23 AND ap.profil_id = 101
  AND NOT EXISTS (SELECT 1 FROM application_profil_page x WHERE x.application_profil_id = ap.id AND x.page_id = h.id);

-- ---------------------------------------------------------------------------
-- 6. Acteurs du ticketing (2 = VALIDATEUR, 8 = INTERVENANT).
--    À COMPLÉTER : remplacer A_COMPLETER par les noms d'utilisateur.
--    Chacun doit avoir un mail. Sans personnel_id, les mails affichent son nom d'utilisateur.
-- ---------------------------------------------------------------------------
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.role_id
FROM users u
JOIN (VALUES
    ('A_COMPLETER', 2),
    ('A_COMPLETER', 8)
) AS r (nom_utilisateur, role_id) ON r.nom_utilisateur = u.nom_utilisateur
WHERE NOT EXISTS (SELECT 1 FROM user_roles x WHERE x.user_id = u.id AND x.role_id = r.role_id);

COMMIT TRANSACTION;
