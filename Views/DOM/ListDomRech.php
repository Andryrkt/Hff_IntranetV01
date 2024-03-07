<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff INtranet</title>
</head>

<style>
    #Contenue {
        width: 100%;
        max-height: 800px;
        overflow-y: auto;
    }

    th {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
    }
</style>

<body>
    <div class="row">

        <form action="">
            <div class="row">
                <div class="col-2">
                    <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                        <span class="input-group-text ">Statut</span>
                        <select name="Statut" id="Statut" class="form-control">
                            <?php foreach ($Statut as $statut) : ?>
                                <option value="<?php echo $statut['LibStatut'] ?>"><?php echo $statut['LibStatut'] ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                        <span class="input-group-text">Matricule</span>
                        <input type="text" name="Matricule" id="Matricule" class="form-control">
                    </div>
                </div>
                <div class="col-3">
                    <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                        <span class="input-group-text">Date de création</span>
                        <input type="date" name="Date_debut" id="Date_debut" class="form-control">
                        <input type="date" name="Date_Fin" id="Date_Fin" class="form-control">
                    </div>
                </div>
                <div class="col-3">
                    <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                        <span class="input-group-text">Date de début</span>
                        <input type="date" name="Date_debut_D" id="Date_debut_D" class="form-control">
                        <input type="date" name="Date_Fin_D" id="Date_Fin_D" class="form-control">
                    </div>
                </div>
                <div class="col-2">
                    <input type="submit" name="export" id="export" class="btn btn-success" value="Export Excel">
                </div>
            </div>

        </form>



    </div>
    <div id="Contenue">
        <table class="table">
            <thead class=" table-dark">
                <tr>
                    <th>Statut</th>
                    <th>Type document</th>
                    <th>N° Ordre Mission</th>
                    <th>Date_Demande</th>
                    <th>Motif_Deplacement</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Mode_Paiement</th>
                    <th>Agence service</th>
                    <th>Date_Debut</th>
                    <th>Date_Fin</th>
                    <th>Nombre_Jour</th>
                    <th>Client</th>
                    <th>N° OR</th>
                    <th>Lieu_Intervention</th>
                    <th>N° Vehicule</th>
                    <th>Total Autres Depenses</th>
                    <th>Total_General_Payer</th>
                    <th>Devis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ListDomRech as $ListDomRech) : ?>
                    <tr>
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
                        <td><?php echo $ListDomRech['Sous_type_document']; ?></td>
                        <td><?php echo $ListDomRech['Numero_Ordre_Mission']; ?></td>
                        <td><?php
                            $dateDemande = $ListDomRech['Date_Demande'];
                            $DDEM = date("d/m/Y", strtotime($dateDemande));
                            echo $DDEM;
                            ?>
                        </td>
                        <td><?php echo $ListDomRech['Motif_Deplacement']; ?></td>
                        <td><?php echo $ListDomRech['Matricule']; ?></td>
                        <td><?php echo $ListDomRech['Nom']; ?></td>
                        <td><?php echo $ListDomRech['Prenom']; ?></td>
                        <td><?php echo $ListDomRech['Mode_Paiement']; ?></td>
                        <td><?php echo $ListDomRech['LibelleCodeAgence_Service']; ?></td>
                        <td><?php
                            $dateDeb = $ListDomRech['Date_Debut'];
                            $DD = date("d/m/Y", strtotime($dateDeb));
                            echo $DD;
                            ?>
                        </td>
                        <td><?php $dateFin = $ListDomRech['Date_Fin'];
                            $DF = date("d/m/Y", strtotime($dateFin));
                            echo $DF;
                            ?></td>
                        <td><?php echo $ListDomRech['Nombre_Jour']; ?></td>
                        <td><?php echo $ListDomRech['Client']; ?></td>
                        <td><?php echo $ListDomRech['Fiche']; ?></td>
                        <td><?php echo $ListDomRech['Lieu_Intervention']; ?></td>
                        <td><?php echo $ListDomRech['NumVehicule']; ?></td>
                        <td><?php echo $ListDomRech['Total_Autres_Depenses']; ?></td>
                        <td><?php echo $ListDomRech['Total_General_Payer']; ?></td>
                        <td><?php echo $ListDomRech['Devis']; ?></td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>