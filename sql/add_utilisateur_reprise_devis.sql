-- ============================================================
-- Migration : Ajout de la colonne utilisateur_reprise
-- Table     : devis_soumis_a_validation_neg
-- Date      : 2026-02-24
-- Description : Stocker le nom de l'utilisateur qui réactive
--               la relance d'un devis (stop → réactivation)
-- ============================================================

ALTER TABLE devis_soumis_a_validation_neg
ADD utilisateur_reprise NVARCHAR(100) NULL;

GO

ALTER TABLE devis_soumis_a_validation_neg
ADD utilisateur_stop NVARCHAR(100) NULL;

GO
