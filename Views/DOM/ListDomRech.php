<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff INtranet</title>
</head>

<body>
    <div class="d-flex  flex-row-reverse  col-2" style="margin-bottom: 1%;margin-left: 1%; text-align: center;">
    
       <select name="Statut" id="Statut" class="form-select" >
        <?php foreach($Statut as $statut):?>
            <option value="<?php echo $statut['LibStatut']?>"><?php echo $statut['LibStatut']?> </option>
        <?php endforeach;?>
       </select>
      
    </div>
    <table class=" table">
        <thead class="table-dark">
            <tr>
                <th style="text-align: center;">ID </th>
                <th style="text-align: center;">Agence - Service </th>
                <th style="text-align: center;">Utilisateur Créateur</th>
                <th style="text-align: center;">Type Document</th>
                <th style="text-align: center;">Sous Type </th>
                <th style="text-align: center;">Matricule concerné</th>
                <th style="text-align: center;">Date de demande </th>
                <th style="text-align: center;">Nombre de Jour</th>
                <th style="text-align: center;">Date début</th>
                <th style="text-align: center;">Date Fin</th>
                <th style="text-align: center;">N° DOM</th>
                <th style="text-align: center;">Statut</th>
                <th style="text-align: center;">Objet</th>
                <th style="text-align: center;">Client </th>
                <th style="text-align: center;">Lieu d'intervention</th>
                <th style="text-align: center;">Montant à payer</th>
                <th style="text-align: center;">Devise</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ListDomRech as $ListDomRech) : ?>
                <tr>
                    <td><?php echo $ListDomRech['ID_Demande_Ordre_Mission'] ?></td>
                    <td><?php echo iconv('Windows-1252', 'UTF-8',  $ListDomRech['LibelleCodeAgence_Service']) ?></td>
                    <td><?php echo $ListDomRech['Nom_Session_Utilisateur'] ?></td>
                    <td><?php echo $ListDomRech['Type_Document'] ?></td>
                    <td><?php echo $ListDomRech['Sous_type_document'] ?></td>
                    <td><?php echo $ListDomRech['Matricule'] ?></td>
                    <td><?php $dateDemande = $ListDomRech['Date_Demande'];
                        $DDEM = date("d/m/Y", strtotime($dateDemande));
                        echo $DDEM;
                        ?></td>
                    <td><?php echo $ListDomRech['Nombre_Jour'] ?></td>
                    <td><?php $dateDeb = $ListDomRech['Date_Debut'];
                        $DD = date("d/m/Y", strtotime($dateDeb));
                        echo $DD;
                        ?></td>
                    <td><?php $dateFin = $ListDomRech['Date_Fin'];
                        $DF = date("d/m/Y", strtotime($dateFin));
                        echo $DF;
                        ?></td>
                    <td><a href="/Hffintranet/index.php?action=DetailDOM&NumDom=<?php echo $ListDomRech['Numero_Ordre_Mission'] ?> "> <?php echo $ListDomRech['Numero_Ordre_Mission'] ?> </a></td>
                    <?php
                    $color_ouvert = "#efd807";
                    $color_compta = "#77b5fe";
                    $color_payer = "#34c924";
                    $statut = strtolower(trim($ListDomRech['Statut']));
                    switch ($statut) {
                        case 'ouvert':
                            $color = $color_ouvert;
                            break;
                        case 'compta':
                            $color = $color_compta;
                            break;
                        default:
                            $color = $color_payer;
                    }

                    ?>
                    <td style="background-color: <?php echo $color; ?>;"><?php echo $ListDomRech['Statut']; ?></td>
                    <td><?php echo $ListDomRech['Motif_Deplacement'] ?></td>
                    <td><?php echo $ListDomRech['Client'] ?></td>
                    <td><?php echo $ListDomRech['Lieu_Intervention'] ?></td>
                    <td><?php echo $ListDomRech['Total_General_Payer'] ?></td>
                    <td><?php echo $ListDomRech['Devis'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>