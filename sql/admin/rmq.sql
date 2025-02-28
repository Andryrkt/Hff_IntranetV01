CREATE TABLE rmq (
    id INT IDENTITY (1, 1),
    description VARCHAR(255),
    date_creation DATE NOT NULL,
    date_modification DATE NOT NULL,
    CONSTRAINT PK_rmq PRIMARY KEY (id),
);

INSERT INTO
    rmq (
        description,
        date_creation,
        date_modification
    )
VALUES (
        'STD',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        '50',
        '2024-07-16',
        '2024-07-16'
    );