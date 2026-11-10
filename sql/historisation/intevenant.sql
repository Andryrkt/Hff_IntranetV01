-- ajout un role intervenant à un utilisateur
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, 8
FROM users u
WHERE u.nom_utilisateur IN ('nom.utilisateur1', 'nom.utilisateur2')
  AND NOT EXISTS (SELECT 1 FROM user_roles x WHERE x.user_id = u.id AND x.role_id = 8);
