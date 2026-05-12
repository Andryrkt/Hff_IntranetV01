# Séparation DIT atelier et pneumatique

L'objectif est de faire une distinction entre les DIT atelier et les DIT pneumatique.

Lors de la création d'une DIT, si la DIT nécessite une intervention de l'atelier pneumatique (réalisé par : ATE POL),
mettre sur le nom du fichier la mention POL : => nouveau nom du fichier DIT : `DITAAMMXXXX_YYZZZ#POL.pdf`.
Ex: DIT26049991_80INF#POL.pdf

Un DIT est réalisé par ATE POL si:

- le champ réalisé par = `ATE POL TANA`
- OU le champ réalisé par = `ATE TANA` ET intervention pneumatique = `OUI`

Le fichier est à déposer dans le répertoire : `192.168.0.28/c$/DOCUWARE/DIT_POL`

Après la création de la DIT POL, au lieu de mettre le statut `AFFECTEE SECTION`, mettre le statut `A VALIDER RESP RENTAL`

# Déploiement en PROD

- version: v1.0
- date: 04-05-2026
- heure: 17:30
- par: [Menja]
