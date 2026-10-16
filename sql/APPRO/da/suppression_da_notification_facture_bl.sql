-- ============================================================
-- Suppression de la table da_notification_facture_bl (SQL Server)
-- Le contrôle de la mise à jour DA après une soumission facture / BL est
-- désormais enregistré dans le suivi des opérations (table
-- historique_operation_etape, étape "Contrôle mise à jour DA (facture / BL)",
-- statut ALERTE en cas d'anomalie).
-- La table n'est plus alimentée ni lue par l'application.
-- À exécuter une fois qu'on n'a plus besoin de l'historique qu'elle contient.
-- ============================================================
DROP TABLE IF EXISTS da_notification_facture_bl;
