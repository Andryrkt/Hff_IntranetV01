ALTER TABLE Personnel
ADD societe VARCHAR(5)

UPDATE Personnel
SET societe = 'HF'


ALTER TABLE Personnel ADD group_direction bit DEFAULT 0