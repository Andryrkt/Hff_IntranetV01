INSERT INTO HFF_INTRANET.dbo.da_article_reappro
(art_constp, art_refp, art_desi, qte_validee_appro, art_pu, code_agence, code_service, date_creation, date_modification)
VALUES
('CEN','4C0001',TRIM('CHAMOISINE                         '),'3','2 470', '80', 'INF', '2025-11-04 00:00:00.000','2025-11-04 00:00:00.000'),
('FBU','1C0012',TRIM('STYLO BLEU                         '),'2','570', '80', 'INF', '2025-11-04 00:00:00.000','2025-11-04 00:00:00.000'),
('FBU','1F0002',TRIM('AGRAFE 24/6                        '),'2','1 235', '80', 'INF', '2025-11-04 00:00:00.000','2025-11-04 00:00:00.000'),
('FBU','1F0034',TRIM('POST IT MM                         '),'1','2 375', '80', 'INF', '2025-11-04 00:00:00.000','2025-11-04 00:00:00.000'),
('FBU','1F0041',TRIM('SCOTCH GM TRANSPARENT              '),'1','20 900', '80', 'INF', '2025-11-04 00:00:00.000','2025-11-04 00:00:00.000')

UPDATE da_article_reappro
SET art_pu = CAST(REPLACE(art_pu, ' ', '') AS INT)
WHERE art_pu LIKE '% %';