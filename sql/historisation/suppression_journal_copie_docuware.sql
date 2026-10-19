-- ============================================================
-- Suppression de la table journal_copie_docuware (SQL Server)
-- Les copies vers DocuWare sont désormais enregistrées dans le suivi des
-- opérations (table historique_operation_etape, lignes "Copie DocuWare : ...").
-- La table n'est plus alimentée ni lue par l'application.
-- À exécuter une fois qu'on n'a plus besoin de l'historique qu'elle contient.
-- ============================================================
DROP TABLE IF EXISTS journal_copie_docuware;
