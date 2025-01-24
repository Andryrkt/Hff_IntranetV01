
INSERT INTO type_document (typeDocument, libelle_document, date_creation, heure_creation, date_modification, heure_modification)
VALUES 
    (
        'DIT', 
        'DEMANDE INTERVENTION', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'OR', 
        'ORDRE DE REPARATION', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'FAC', 
        'FACTURE', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'RI', 
        'RAPPORT INTERVENTION', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'TIK', 
        'DEMANDE DE SUPPORT INFORMATIQUE', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'DA', 
        'DEMANDE APPROVISIONNEMENT', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'DOM', 
        'DEMANDE ORDRE DE MISSION', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'BADM', 
        'MOUVEMENT MATERIEL BADM', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'CAS', 
        'CASIER', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'CDE', 
        'COMMANDE', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'DEV', 
        'DEVIS', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'BC', 
        'BON DE COMMANDE', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    ),
    (
        'AC', 
        'ACCUSE DE RECEPTION', 
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ()),
        CONVERT(DATE, GETDATE ()),
        CONVERT(TIME, GETDATE ())
    )
;