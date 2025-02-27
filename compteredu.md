# INTERFACE

- choix atelier (formulaire DIT)
- soumission Devis
- affichage dynamique de choix (DocSoumisDW) selon les critères
- Overlay sur le cloture DIT
- overlay dans tous les pages
- changement du contenu du modal sur le numero OR de la liste DIT (pas encore fini mais à revoire)
- des changmenet vient de la branch ticketing (historique des pages, affichage et cache de mot de passe)
- page de garde devis

# Back-end

- amelioration du code javascript liste dit
- separation de code pour le planningModel et modalPlanningModel
- resolution bug dom sur le frais exceptionnel (okey)

10/01/2025
DEVIS EXTERNE
- ajout colonne montantForfait DECIMAL(18, 2) et natureOperation VARCHAR(3) (fait)
- Quand l’utilisateur clique sur « Enregistrer », mettre le message suivant pour notifier
l’utilisateur de l’opération qu’il va exécuter « Vous êtes en train de soumettre le devis N°
&lt;numero_devis&gt; à validation dans DocuWare… &lt;retour chariot&gt;Veuillez de pas fermer
l’onglet durant le traitement »
- Si statut DIT <> « AFFECTEE SECTION » ALORS Bloquer la soumission => « Impossible de soumettre le devis »
- Si le statut de dernier devis soumis = « Soumis à validation » ALORS Bloquer la soumission => « Impossible de soumettre le devis car un devis est déjà en cours de validation »
