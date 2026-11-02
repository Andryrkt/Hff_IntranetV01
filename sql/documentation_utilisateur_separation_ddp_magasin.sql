-- ============================================================
-- documentation_utilisateur : le guide 'ddp-magasin' est séparé en deux,
-- 'ddp-magasin-avance' et 'ddp-magasin-apres-arrivage'.
-- Les deux pages reprennent la rubrique et la publication de l'ancienne,
-- qui repasse en brouillon (conservée, visible des administrateurs).
-- À exécuter après documentation_utilisateur_rubrique_publication.sql.
-- Rejouable : une page déjà créée n'est pas modifiée.
-- ============================================================
IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-avance')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
SELECT 'ddp-magasin-avance', N'Demande de paiement magasin – à l''avance', rubrique, 0, publie, N'
<h2>Présentation</h2>
<p>La demande de paiement à l''avance (DDPA) sert à payer un acompte à un fournisseur, sur facture proforma, quand la marchandise n''est pas encore arrivée. Une demande ne concerne qu''une commande, validée mais pas encore réceptionnée.</p>
<p>Elle se remplit dans l''intranet et génère un PDF, puis vous la transmettez en validation dans DocuWare depuis l''écran Consultation facture.</p>
<p>Si la marchandise est arrivée, utilisez plutôt la <a href="ddp-magasin-apres-arrivage">demande de paiement après arrivage</a>.</p>

<h2>Remplir la demande</h2>
<p>Menu <strong>Compta › Demande de paiement › Nouvelle demande de paiement à l''avance</strong>.</p>
<ol>
<li><strong>Fournisseur</strong> : tapez le numéro ou le nom, puis choisissez dans la liste. Le RIB et les commandes se chargent.</li>
<li><strong>Mode de paiement, devise, motif</strong> : complétez-les.</li>
<li><strong>N° commande</strong> : choisissez la commande. Seules les commandes validées et pas encore réceptionnées apparaissent.</li>
<li>L''<strong>historique des demandes de paiement</strong> de cette commande s''affiche en haut : demandes déjà faites, % de chacune, cumul, total déjà validé et reste.</li>
<li><strong>Montant facture proforma *</strong> : le montant à payer au fournisseur, tel qu''il figure sur la proforma.</li>
<li><strong>Montant total du commande *</strong> : rempli automatiquement depuis IPS dès que vous choisissez la commande. Il n''est pas modifiable, et sans montant dans IPS l''enregistrement est bloqué.</li>
<li><strong>Pièces jointes</strong> : la proforma facture fournisseur et le RIB fournisseur sont attendus ; les autres documents sont facultatifs. Les BC client rattachés à la commande s''affichent dans leur onglet ; leur dépôt manuel est désactivé.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<p>Le pourcentage payé de la demande vaut montant facture proforma ÷ montant total du commande × 100. Le montant facture proforma ne peut pas dépasser 20 000.</p>
<p><strong>Blocage :</strong> si les demandes à l''avance déjà validées atteignent 100 % de la commande, l''enregistrement est refusé. La commande est soldée par acomptes ; le reste se traite par une demande après arrivage.</p>

<h2>Après l''enregistrement : transmettre en validation</h2>
<p>L''enregistrement ne lance pas la validation : la demande reste au statut « DDPA à transmettre » tant qu''elle n''est pas transmise.</p>
<ol>
<li>Après l''enregistrement, vous arrivez sur l''écran <strong>Magasin › Consultation facture</strong>.</li>
<li>Cochez les demandes à transmettre, puis cliquez sur <strong>Transmettre à validation</strong>.</li>
<li>Les demandes cochées reçoivent un même numéro CLA, passent au statut « Soumis à validation », et leur PDF est déposé dans DocuWare.</li>
</ol>

<h2>Messages d''erreur et que faire</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Cause</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>La commande X est déjà payée à 100 % par des demandes de paiement à l''avance validées</td><td>La commande est déjà soldée par acomptes</td><td>Ne pas faire de nouvel acompte ; utiliser le paiement après arrivage à la réception</td></tr>
<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td><td>Le plafond est dépassé</td><td>Saisir un montant inférieur ou égal au plafond</td></tr>
<tr><td>Le montant total de cette commande est introuvable dans IPS pour ce fournisseur</td><td>La commande n''existe pas pour ce fournisseur dans IPS, ou son montant est nul. L''enregistrement est bloqué</td><td>Vérifier le fournisseur et la commande dans IPS</td></tr>
<tr><td>Aucune demande DDP/BAP à transmettre n''a été reçue</td><td>La transmission n''a reçu aucun numéro de demande. Rien n''est transmis</td><td>Recharger la page, cocher de nouveau les demandes, puis signaler le problème s''il revient</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
</tbody>
</table>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
</tbody>
</table>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, elle ne compte ni pour le blocage à 100 %, ni pour le paiement après arrivage, où son montant n''est pas déduit.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
SELECT 'ddp-magasin-apres-arrivage', N'Demande de paiement magasin – après arrivage', rubrique, 1, publie, N'
<h2>Présentation</h2>
<p>La demande de paiement après arrivage sert à payer un fournisseur quand la marchandise est arrivée et que sa facture est dans GCOT. Vous partez des factures, les commandes suivent.</p>
<p>Elle se remplit dans l''intranet et génère un PDF, puis vous la transmettez en validation dans DocuWare depuis l''écran Consultation facture.</p>
<p>Une saisie crée une seule demande, même si elle couvre plusieurs factures et commandes. Le montant à payer tient compte, commande par commande, des acomptes déjà validés.</p>
<p>Si la marchandise n''est pas encore arrivée, utilisez plutôt la <a href="ddp-magasin-avance">demande de paiement à l''avance</a>.</p>

<h2>Remplir la demande</h2>
<p>Menu <strong>Compta › Demande de paiement › Nouvelle demande de paiement après arrivage</strong>.</p>
<ol>
<li><strong>Fournisseur, mode de paiement, devise, motif</strong> : tapez le numéro ou le nom du fournisseur, choisissez-le dans la liste, puis complétez les autres champs.</li>
<li><strong>N° facture fournisseur</strong> : choisissez une ou plusieurs factures. Les commandes correspondantes sont sélectionnées automatiquement.</li>
<li>Le <strong>tableau des commandes</strong> apparaît, une ligne par commande (colonnes ci-dessous).</li>
<li>Dans chaque ligne, vérifiez le <strong>montant du commande réceptionné</strong> : il vient d''IPS et n''est pas modifiable. Le pourcentage en rouge, devant le montant, est sa part du montant total de la commande. Si IPS ne trouve aucun montant pour une commande, « - » s''affiche et l''enregistrement est bloqué.</li>
<li><strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants du commande réceptionnés du tableau. Le <strong>montant total à payer</strong>, non modifiable, affiche la somme des montants du commande à payer. Le pourcentage en rouge à côté de ces deux libellés est leur part du montant total des commandes.</li>
<li><strong>Pièces jointes</strong> : le contrôle livraison et le RIB fournisseur sont attendus ; les documents de douane et de commande DocuWare sont ajoutés automatiquement. Les autres documents sont facultatifs.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<table border="1" cellpadding="4">
<thead><tr><th>Colonne</th><th>Ce qu''elle montre</th><th>À saisir</th></tr></thead>
<tbody>
<tr><td>N° commande</td><td>La commande liée aux factures choisies</td><td>Non</td></tr>
<tr><td>N° facture</td><td>La ou les factures de cette commande, une par ligne</td><td>Non</td></tr>
<tr><td>N° dossier douane, N° LTA, N° HAWB</td><td>Les références du dossier de douane de chaque facture</td><td>Non</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande (voir section suivante)</td><td>Non</td></tr>
<tr><td>Montant du commande réceptionné</td><td>Le montant réceptionné pour cette commande dans IPS, précédé de son % du montant total de la commande</td><td>Non</td></tr>
<tr><td>Montant du commande à payer</td><td>Précédé de son % du montant total de la commande. Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance. Toujours 0 si la commande est déjà payée à 100 % par acomptes</td><td>Non</td></tr>
<tr><td>Solde avance restant</td><td>Calculé : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0</td><td>Non</td></tr>
</tbody>
</table>

<h2>Ce qui est créé à l''enregistrement</h2>
<p>Une saisie crée une seule demande de paiement (DDPL) au statut « DDPL à transmettre », avec un seul numéro et une seule page de garde, quels que soient le nombre de factures et de commandes.</p>
<p>Le montant à payer se calcule commande par commande, selon les acomptes déjà validés :</p>
<table border="1" cellpadding="4">
<thead><tr><th>% DDPA validé de la commande</th><th>Solde avance</th><th>Montant du commande à payer</th></tr></thead>
<tbody>
<tr><td>0 %</td><td>0</td><td>Le montant réceptionné</td></tr>
<tr><td>Entre 0 et 100 %</td><td>Calculé</td><td>0 si le solde couvre le montant réceptionné, sinon montant réceptionné − solde avance</td></tr>
<tr><td>100 %</td><td>0</td><td>0</td></tr>
</tbody>
</table>
<ul>
<li><strong>Montant à payer de la demande :</strong> le total des montants du commande à payer. Une demande à 0 passe quand même en validation.</li>
<li><strong>PDF :</strong> la page de garde reprend le résumé des BC, le récapitulatif des livraisons et celui des demandes de paiement déjà enregistrées, puis est fusionnée avec toutes les pièces jointes. Les pourcentages sont imprimés en rouge devant les montants : dans le résumé des BC et dans la case « Montant à payer ». Le PDF n''est déposé dans DocuWare qu''à la transmission.</li>
</ul>
<p>Le solde avance de chaque commande se calcule ainsi :</p>
<p><strong>Solde avance = Montant total du commande − (somme des proformas des DDPA + montant déjà payé)</strong>, ramené à 0 s''il est négatif.</p>
<p>Le montant total du commande est celui de la DDPA la plus récente ; le montant déjà payé additionne les montants du commande réceptionnés des demandes validées qui ne sont pas des DDPA. Le solde et le montant du commande à payer sont enregistrés et imprimés sur le PDF.</p>

<h2>Après l''enregistrement : transmettre en validation</h2>
<p>L''enregistrement ne lance pas la validation : la demande reste au statut « … à transmettre » tant qu''elle n''est pas transmise.</p>
<ol>
<li>Après l''enregistrement, vous arrivez sur l''écran <strong>Magasin › Consultation facture</strong>.</li>
<li>Cochez les demandes à transmettre, puis cliquez sur <strong>Transmettre à validation</strong>.</li>
<li>Les demandes cochées reçoivent un même numéro CLA, passent au statut « Soumis à validation », et leur PDF est déposé dans DocuWare.</li>
</ol>

<h2>Messages d''erreur et que faire</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Cause</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>Le montant réceptionné de la commande X est introuvable dans IPS pour la/les facture(s) sélectionnée(s)</td><td>IPS ne trouve aucune réception de cette commande pour ces factures. L''enregistrement est bloqué</td><td>Vérifier le numéro de facture dans GCOT et IPS, ainsi que la réception</td></tr>
<tr><td>Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s)</td><td>Le montant total réceptionné diffère de la somme des montants du commande réceptionnés</td><td>Saisir exactement la somme du tableau</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
<tr><td>Aucune demande DDP/BAP à transmettre n''a été reçue</td><td>La transmission n''a reçu aucun numéro de demande. Rien n''est transmis</td><td>Recharger la page, cocher de nouveau les demandes, puis signaler le problème s''il revient</td></tr>
</tbody>
</table>
<p>Après une erreur, le formulaire se recharge : vérifiez vos saisies, notamment le montant total réceptionné.</p>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>DDPL</td><td>Demande de paiement après arrivage, créée par ce formulaire</td></tr>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur une commande (calcul dans la section « Ce qui est créé à l''enregistrement »)</td></tr>
</tbody>
</table>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, son montant n''est pas déduit du montant à payer.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

-- L'ancienne page n'est plus proposée aux utilisateurs une fois les deux pages créées
IF EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-avance')
    AND EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
    UPDATE documentation_utilisateur SET publie = 0 WHERE slug = 'ddp-magasin';

-- Consultation facture : écran de transmission indiqué par les deux pages ci-dessus.
-- Publication et ajout des DDPA, qui peuvent aussi être transmises depuis cet écran.
UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu, N'(BAP, DDPL, DDPR', N'(DDPA, BAP, DDPL, DDPR'), N'(BAP, DDPL ou DDPR', N'(DDPA, BAP, DDPL ou DDPR'),
    publie = 1
WHERE slug = 'magasin-consultation-facture';
