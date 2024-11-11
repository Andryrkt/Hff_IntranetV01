EXEC sp_rename 'TKI_SOUS_CATEGORIE.Date_Creation',
'date_creation',
'COLUMN';

ALTER TABLE TKI_SOUS_CATEGORIE ADD date_modification DATE