-- demande_paiement.numero_commande contient la liste des commandes séparées par ';'
-- (DDP après arrivage multi-commandes) : la taille d'origine est trop petite.
-- Passage en NVARCHAR(MAX), en conservant le NULL / NOT NULL actuel.

DECLARE @nullable NVARCHAR(10) = (
    SELECT CASE WHEN is_nullable = 1 THEN 'NULL' ELSE 'NOT NULL' END
    FROM sys.columns
    WHERE object_id = OBJECT_ID('dbo.demande_paiement') AND name = 'numero_commande'
);

EXEC ('ALTER TABLE dbo.demande_paiement ALTER COLUMN numero_commande NVARCHAR(MAX) ' + @nullable);

-- vérification
SELECT name, TYPE_NAME(user_type_id) AS type, max_length, is_nullable
FROM sys.columns
WHERE object_id = OBJECT_ID('dbo.demande_paiement') AND name = 'numero_commande';
