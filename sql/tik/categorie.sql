EXEC sp_rename 'TKI_CATEGORIE.Date_Creation',
'date_creation',
'COLUMN';

INSERT INTO
    TKI_CATEGORIE (
        Description,
        date_creation,
        date_modification
    )
VALUES (
        'APPLICATION METIER',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'MATERIELS',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'BUREAUTIQUE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SECURITE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'MESSAGERIE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'RESEAU INFORMATIQUE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SERVICE DIVERS',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SERVICE INTERNET',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'REPORTING',
        '2024-11-07',
        '2024-11-07'
    )