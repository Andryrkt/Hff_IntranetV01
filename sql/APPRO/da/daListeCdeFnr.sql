ALTER TABLE cdefnr_soumis_a_validation
DROP COLUMN libelle_fournisseur, date_commande, montant_commande, devise_commande, est_facture;

ALTER TABLE cdefnr_soumis_a_validation
ADD nom_fichier VARCHAR(255);

ALTER TABLE cdefnr_soumis_a_validation
ADD numero_da VARCHAR(11);