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
<li><strong>Montant total du commande *</strong> : le montant total de la commande.</li>
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
<tr><td>Le montant total du commande est obligatoire</td><td>Champ vide</td><td>Saisir le montant total de la commande</td></tr>
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
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, elle ne compte ni pour le blocage à 100 %, ni pour le paiement après arrivage, où la commande part en BAP.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
SELECT 'ddp-magasin-apres-arrivage', N'Demande de paiement magasin – après arrivage', rubrique, 1, publie, N'
<h2>Présentation</h2>
<p>La demande de paiement après arrivage sert à payer un fournisseur quand la marchandise est arrivée et que sa facture est dans GCOT. Vous partez des factures, les commandes suivent.</p>
<p>Elle se remplit dans l''intranet et génère un PDF, puis vous la transmettez en validation dans DocuWare depuis l''écran Consultation facture.</p>
<p>Le formulaire choisit tout seul le bon type de document (BAP, DDPR ou DDPL) selon les acomptes déjà validés sur chaque commande. Vous n''avez rien à choisir.</p>
<p>Si la marchandise n''est pas encore arrivée, utilisez plutôt la <a href="ddp-magasin-avance">demande de paiement à l''avance</a>.</p>

<h2>Remplir la demande</h2>
<p>Menu <strong>Compta › Demande de paiement › Nouvelle demande de paiement après arrivage</strong>.</p>
<ol>
<li><strong>Fournisseur, mode de paiement, devise, motif</strong> : tapez le numéro ou le nom du fournisseur, choisissez-le dans la liste, puis complétez les autres champs.</li>
<li><strong>N° facture fournisseur</strong> : choisissez une ou plusieurs factures. Les commandes correspondantes sont sélectionnées automatiquement.</li>
<li>Le <strong>tableau des commandes</strong> apparaît, une ligne par commande (colonnes ci-dessous).</li>
<li>Dans chaque ligne, vérifiez le <strong>montant du commande réceptionné</strong> : il est pré-rempli avec le montant réceptionné et reste modifiable. Il est obligatoire pour toutes les commandes.</li>
<li><strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants du commande réceptionnés du tableau. Le <strong>montant total à payer</strong>, non modifiable, affiche la somme des montants du commande à payer.</li>
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
<tr><td>Montant du commande réceptionné *</td><td>Le montant réceptionné pour cette commande, pré-rempli et modifiable</td><td><strong>Oui</strong></td></tr>
<tr><td>Montant du commande à payer</td><td>Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance</td><td>Non</td></tr>
<tr><td>Solde avance restant</td><td>Calculé : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0</td><td>Non</td></tr>
</tbody>
</table>

<h2>Ce qui est créé à l''enregistrement</h2>
<p>À l''enregistrement, chaque commande reçoit le document qui correspond aux acomptes déjà validés sur elle.</p>
<table border="1" cellpadding="4">
<thead><tr><th>% DDPA validé de la commande</th><th>Document créé</th><th>PDF</th><th>Statut</th><th>Solde avance</th></tr></thead>
<tbody>
<tr><td>0 %</td><td><strong>BAP</strong> – bon à payer</td><td>BAP MAGASIN</td><td>BAP à transmettre</td><td>0</td></tr>
<tr><td>Entre 0 et 100 %</td><td><strong>DDPL</strong> – après arrivage</td><td>DDP</td><td>DDPL à transmettre</td><td>Calculé</td></tr>
<tr><td>100 %</td><td><strong>DDPR</strong> – paiement régul</td><td>DDP</td><td>DDPR à transmettre</td><td>0</td></tr>
</tbody>
</table>
<ul>
<li><strong>Plusieurs situations dans une saisie :</strong> les commandes sont regroupées par type. Une demande est créée par groupe, avec son propre numéro, ses factures, son PDF et une copie de vos pièces jointes.</li>
<li><strong>Montant à payer d''une demande :</strong> le total des montants du commande à payer de son groupe. Une DDPR a toujours un montant à payer de 0, et une demande à 0 passe quand même en validation.</li>
<li><strong>PDF :</strong> la page de garde (BAP MAGASIN ou DDP) est fusionnée avec toutes les pièces jointes. Le PDF n''est déposé dans DocuWare qu''à la transmission.</li>
<li><strong>BAP MAGASIN :</strong> résumé des BC (factures, dossiers de douane, montants), livraisons, demandes de paiement déjà faites et montant BAP.</li>
</ul>
<p>Pour une DDPL, le solde avance de chaque commande se calcule ainsi :</p>
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
<tr><td>Le montant du commande réceptionné X est obligatoire</td><td>Une ligne du tableau est vide</td><td>Saisir un montant sur chaque ligne</td></tr>
<tr><td>Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s)</td><td>Le montant total réceptionné diffère de la somme des montants du commande réceptionnés</td><td>Saisir exactement la somme du tableau</td></tr>
<tr><td>La facture X concerne des commandes de situations différentes</td><td>Une même facture couvre des commandes qui donneraient des documents différents (ex. une en BAP, une en DDPL)</td><td>Contacter la comptabilité pour traiter cette facture à part</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
</tbody>
</table>
<p>Après une erreur, le formulaire se recharge : resaisissez les montants du tableau des commandes.</p>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>BAP</td><td>Bon à payer : paiement après arrivage d''une commande sans acompte validé</td></tr>
<tr><td>DDPR</td><td>Demande de paiement régul : paiement après arrivage d''une commande déjà payée à 100 % par acomptes</td></tr>
<tr><td>DDPL</td><td>Demande de paiement après arrivage d''une commande partiellement payée par acomptes</td></tr>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur une commande (calcul dans la section « Ce qui est créé à l''enregistrement »)</td></tr>
</tbody>
</table>
<p><strong>Pourquoi ai-je reçu plusieurs numéros de demande pour une seule saisie ?</strong> Vos factures couvraient des commandes de situations différentes. Une demande est créée par type de document ; tous les numéros s''affichent dans le message de confirmation.</p>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, la commande part en BAP.</p>
<p><strong>Puis-je choisir moi-même BAP, DDPR ou DDPL ?</strong> Non. Le type dépend uniquement des acomptes validés sur la commande ; il apparaît dans la liste Consultation facture après l''enregistrement.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

-- L'ancienne page n'est plus proposée aux utilisateurs une fois les deux pages créées
IF EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-avance')
    AND EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
    UPDATE documentation_utilisateur SET publie = 0 WHERE slug = 'ddp-magasin';

-- Page après arrivage déjà créée : colonnes DDPA et DDP à générer retirées du tableau,
-- montants renommés, colonne calculée ajoutée, solde avance placé avant les montants.
DECLARE @g NVARCHAR(MAX) = (SELECT contenu FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage');
SET @g = REPLACE(@g, N'<tr><td>DDPA</td><td>Le montant des proformas des demandes à l''avance validées sur la commande</td><td>Non</td></tr>', N'');
SET @g = REPLACE(@g, N'<tr><td>DDP à générer</td><td>Le document qui sera créé : BAP, DPR (DDPR) ou DPL (DDPL)</td><td>Non</td></tr>', N'');
SET @g = REPLACE(@g, N' ; la colonne « DDP à générer » l''annonce avant l''enregistrement.', N' ; il apparaît dans la liste Consultation facture après l''enregistrement.');
SET @g = REPLACE(@g, N'vérifiez le <strong>montant du commande à payer</strong>', N'vérifiez le <strong>montant du commande réceptionné</strong>');
SET @g = REPLACE(@g, N'<strong>Montant à payer *</strong> : il doit être égal à la somme des montants du commande à payer du tableau.', N'<strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants du commande réceptionnés du tableau.');
SET @g = REPLACE(@g, N'<tr><td>Montant du commande à payer *</td><td>Le montant à payer pour cette commande</td><td><strong>Oui</strong></td></tr>', N'<tr><td>Montant du commande réceptionné *</td><td>Le montant réceptionné pour cette commande, pré-rempli et modifiable</td><td><strong>Oui</strong></td></tr><tr><td>Montant du commande à payer</td><td>Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance</td><td>Non</td></tr>');
SET @g = REPLACE(@g, N'Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon solde avance − montant réceptionné. Information affichée, non enregistrée', N'Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance');
SET @g = REPLACE(@g, N'additionne les montants du commande à payer des demandes', N'additionne les montants du commande réceptionnés des demandes');
SET @g = REPLACE(@g, N'<td>Le montant du commande à payer X est obligatoire</td>', N'<td>Le montant du commande réceptionné X est obligatoire</td>');
SET @g = REPLACE(@g, N'<td>Le montant à payer diffère de la somme des montants du commande à payer</td>', N'<td>Le montant total réceptionné diffère de la somme des montants du commande réceptionnés</td>');
SET @g = REPLACE(@g, N'<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande (voir section suivante)</td><td>Non</td></tr>', N'');
SET @g = REPLACE(@g, N'<tr><td>Montant du commande réceptionné *</td>', N'<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande (voir section suivante)</td><td>Non</td></tr><tr><td>Montant du commande réceptionné *</td>');
SET @g = REPLACE(@g, N'<li><strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants du commande réceptionnés du tableau.</li>', N'<li><strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants du commande réceptionnés du tableau. Le <strong>montant total à payer</strong>, non modifiable, affiche la somme des montants du commande à payer.</li>');
SET @g = REPLACE(@g, N'Le solde est enregistré et imprimé sur le PDF.', N'Le solde et le montant du commande à payer sont enregistrés et imprimés sur le PDF.');
SET @g = REPLACE(@g, N'<li><strong>Montant à payer d''une demande :</strong> le total des factures de son groupe, même s''il diffère du montant saisi.</li>', N'<li><strong>Montant à payer d''une demande :</strong> le total des montants du commande à payer de son groupe.</li>');
SET @g = REPLACE(@g, N'<tr><td>Solde avance restant</td><td>Calculé : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0</td><td>Non</td></tr>', N'');
SET @g = REPLACE(@g, N'<tr><td>Montant du commande à payer</td><td>Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance</td><td>Non</td></tr>', N'<tr><td>Montant du commande à payer</td><td>Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance</td><td>Non</td></tr><tr><td>Solde avance restant</td><td>Calculé : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0</td><td>Non</td></tr>');
SET @g = REPLACE(@g, N'<li><strong>Montant à payer d''une demande :</strong> le total des montants du commande à payer de son groupe.</li>', N'<li><strong>Montant à payer d''une demande :</strong> le total des montants du commande à payer de son groupe. Une DDPR a toujours un montant à payer de 0, et une demande à 0 passe quand même en validation.</li>');
SET @g = REPLACE(@g, N'<p><strong>Solde avance = Montant total du commande − (somme des proformas des DDPA + montant déjà payé)</strong></p>', N'<p><strong>Solde avance = Montant total du commande − (somme des proformas des DDPA + montant déjà payé)</strong>, ramené à 0 s''il est négatif.</p>');
UPDATE documentation_utilisateur SET contenu = @g WHERE slug = 'ddp-magasin-apres-arrivage';

-- Consultation facture : écran de transmission indiqué par les deux pages ci-dessus.
-- Publication et ajout des DDPA, qui peuvent aussi être transmises depuis cet écran.
UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu, N'(BAP, DDPL, DDPR', N'(DDPA, BAP, DDPL, DDPR'), N'(BAP, DDPL ou DDPR', N'(DDPA, BAP, DDPL ou DDPR'),
    publie = 1
WHERE slug = 'magasin-consultation-facture';
