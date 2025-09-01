soumission de l'or soumis a validation (remplissage des donnée dans le pdf)
- tester s'il existe déjà une or soumi dans la table or_soumis_a_validation pour le numero or entrer par l'utilisateur
- si le teste retourne vide ou null dans dans le pdf les montants avant sont vide
- sinon, récupérer tous les contenus de la ligne qui a le numero de version le plus éléver (V-1)
        - en même temps recupérer les contenus de la ligne du numero OR entrer par l'utilisateur dans informix (V)
        - faire la comparaison de (V-1) et (V) pour donner la statut