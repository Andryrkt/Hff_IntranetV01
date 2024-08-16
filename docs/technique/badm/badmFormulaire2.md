## INITIALISATION du formulaire selon le type de mouvement

type de mouvement : ENTREE EN PARC
changer le service emetteur service emetteur => COM COMMERCIAL

# Autorlisation

- l'utilisateur qui a une rôle administrateur est toujour autoriser

## Autorisation pour crée ou consulter une badm

- si l'application est dans l'application autoriser pour l'utilisateur

## Autorisation pour crée une badm

- si l'agence du materiel (agence emetteur) est dans l'agence autoriser pour l'utilisateur
- si le service du materiel (service emetteur) est dans le service autoriser pour l'utilsiateur (cas particuler pour les matériel mise en parc qui a pour service COM COMMERCIAL)

## Codition de creation pdf et inserssion dans la base de donnée formulaire 2

### type de mouvement : SESSION D'ACTIF

- si agence du materiel (agence emetteur) est égale à (90, 91 , 92) donc agence destiantaire = '90' et service destiantaire = 'COM' sinon agence Destinataire = '01' et serviceDestinatire = 'COM'
