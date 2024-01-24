<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Ordre</title>
</head>
<script>
    function visible() {
        var select = document.getElementById('typeMission');
        var labelINput = document.getElementById('labAutre');
        var input = document.getElementById('AutreType');
        if (select.value == "AUTRES A PRECISER") {
            labelINput.style.display = 'block';
            input.style.display = 'block';

        } else {
            labelINput.style.display = 'none';
            input.style.display = 'none';
        }
    }

    function recupeVal() {
        var DateD = document.getElementById('dateDebut').value;
        var DateF = document.getElementById('dateFin').value;

        var StartDate = new Date(DateD);
        var EndDate = new Date(DateF);
        var DiffDate = (EndDate - StartDate) / (1000 * 60 * 60 * 24) + 1;
        document.getElementById('Nbjour').value = DiffDate;
    }

    function visible_espece() {
        var mode = document.getElementById('modepaie').value;
        if (mode === "ESPECES") {
            document.getElementById('modeMob').style.display = "none";
            document.getElementById('modecompte').style.display = "none";
            document.getElementById('modeespece').style.display = "block";
            document.getElementById('labelMode').innerHTML = "ESPECES";
        }
        if (mode === "MOBILE MONEY") {
            document.getElementById('modeMob').style.display = "block";
            document.getElementById('modeespece').style.display = "none";
            document.getElementById('modecompte').style.display = 'none';
            document.getElementById('labelMode').innerHTML = "MOBILE MONEY";
        }
        if (mode === "VIREMENT BANCAIRE") {
            document.getElementById('modeespece').style.display = "none";
            document.getElementById('modeMob').style.display = "none";
            document.getElementById('modecompte').style.display = "block";
            document.getElementById('labelMode').innerHTML = "VIREMENT BANCAIRE";
        }

    }

    function indemnité() {
        var idemn = document.getElementById('idemForfait').value;
        var nbjour = document.getElementById('Nbjour').value;

        var total = idemn * nbjour
        document.getElementById('TotalidemForfait').value = total;
    }

    function use_number(node) {
        var empty_val = false;
        const value = node.value;
        if (node.value == '')
            empty_val = true;
        node.type = 'number';
        /* if (!empty_val)
             node.value = Number(value.replace(/,/g, '')); */
    }

    function use_text(node) {
        var empty_val = false;
        const value = Number(node.value);
        if (node.value == '')
            empty_val = true;
        node.type = 'text';
        if (!empty_val)
            node.value = value.toLocaleString();

    }

    function Somme() {
        var mont01 = document.getElementById('Autredep1').value;
        var mont02 = document.getElementById('Autredep2').value;
        var mont03 = document.getElementById('Autredep3').value;
        var montIndemTotal = document.getElementById('TotalidemForfait').value;
       
        if (mont01 === "") {
            mont01 = 0
        }
        if (mont02 === "") {
            mont02 = 0
        }
        if (mont03 === "") {
            mont03 = 0
        }

       document.getElementById('TotalAutredep').value = parseInt(mont01) + parseInt(mont02) + parseInt(mont03);

        document.getElementById('Alldepense').value = parseInt(document.getElementById('TotalAutredep').value) + parseInt(montIndemTotal);
    }
</script>

<body onload="visible_espece(); visible()"><!--/Hff_IntranetV01/Views/tcpdf/examples/Flight_brief_pdf.php-->
    <div class="container">
        <form action="/Hff_IntranetV01/index.php?action=EnvoyerImprime" method="POST">
            <div class="row">
                <div class="col">
                    <label for="NumDOM" class="label-form">N° DOM</label>
                    <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $NumDom ?>">
                </div>
                <div class="col">
                    <label for="datesyst" class="label-form"> Date</label>
                    <input type="date" name="datesyst" id="modeesp" class="form-control" value="<?php echo $datesyst ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="Serv" class="label-form">Code :</label>
                    <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $code_service ?>">
                </div>
                <div class="col">
                    <label for="LibServ" class="label-form">Service :</label>
                    <input type="text" name="LibServ" class="form-control" id="LibServ" value="<?php echo $service ?>">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="typeMission" class="label-form"> Type de Mission</label>
                    <input name="typeMission" id="typeMission" class="form-control" value="<?php echo $typeMission ?>" />

                </div>
                <div class="col">
                    <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                    <input type="text" name="AutreType" class="form-control" id="AutreType" value="<?php echo $autrtype ?>">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="matricule" class="label-form"> Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="form-control" value="<?php echo $Maricule ?>">
                </div>
            </div>
            <div class="row" >  
                <?php foreach ($Noms as $Noms) : ?>
                    <div class="col">
                        <label for="Nomprenoms" class="label-form"> Nom </label>
                        <input name="nomprenom" id="nomprenom" class="form-control" value="<?php echo $Noms['Nom'] ?>" />
                    </div>
                    <div class="col">
                        <label for="prenoms" class="label-form"> Prénoms </label>
                        <input name="prenom" id="prenom" class="form-control" value="<?php echo $Noms['Prenoms'] ?>" />
                    </div>
                <?php endforeach; ?>
            </div>



            <div class="row">
                <div class="col">
                    <label for="dateDebut" class="label-form"> Date début</label>
                    <input type="date" name="dateDebut" id="dateDebut" class="form-control" required>
                </div>
                <div class="col">
                    <label for="heureDebut" class="label-form"> Heure début</label>
                    <input type="time" name="heureDebut" id="heureDebut" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="dateFin" class="label-form"> Date Fin</label>
                    <input type="date" name="dateFin" id="dateFin" class="form-control" onblur="recupeVal()" required>
                </div>
                <div class="col">
                    <label for="heureFin" class="label-form"> Heure Fin</label>
                    <input type="time" name="heureFin" id="heureFin" class="form-control" required>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="periode" class="label-form" id="nomprenom"> Période</label>
                    <input type="text" name="Nbjour" id="Nbjour" class="form-control" required style="text-align: right;" readonly>
                </div>

                <div class="col">
                    <label for="motif" class="label-form"> Motif</label>
                    <input type="text" name="motif" id="motif" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="client" class="label-form"> Client</label>
                    <input type="text" name="client" id="client" class="form-control">
                </div>
                <div class="col">
                    <label for="fiche" class="label-form"> N°fiche</label>
                    <input type="text" name="fiche" id="fiche" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="lieuInterv" class="label-form"> Lieu D'intervention</label>
                    <input type="text" name="lieuInterv" id="lieuInterv" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="vehicule" class="label-form"> Véhicule Société</label>
                    <select name="vehicule" id="vehicule" class="form-select">
                        <option value="OUI">OUI</option>
                        <option value="NON">NON</option>
                    </select>
                </div>
                <div class="col">
                    <label for="N_vehicule" class="label-form"> N°</label>
                    <input type="text" name="N_vehicule" id="N_vehicule" class="form-control" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="idemForfait" class="label-form"> Indemnité Forfaitaire</label>
                    <input type="number" name="idemForfait" id="idemForfait" class="form-control" onblur="indemnité()" required />
                </div>
                <div class="col">
                    <label for="TotalidemForfait" class="label-form"> Total d'Indemnité Forfaitaire</label>
                    <input type="number" name="TotalidemForfait" id="TotalidemForfait" class="form-control" required />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep" class="label-form"> Motif Autre dépense 1</label>
                    <input type="text" name="MotifAutredep" id="MotifAutredep" class="form-control">
                </div>
                <div class="col">
                    <label for="Autredep1" class="label-form"> Montant </label>
                    <input type="text" name="Autredep1" id="Autredep1" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep2" class="label-form"> Motif Autre dépense 2</label>
                    <input type="text" name="MotifAutredep2" id="MotifAutredep2" class="form-control" >
                </div>
                <div class="col">
                    <label for="Autredep2" class="label-form"> Montant </label>
                    <input type="text" name="Autredep2" id="Autredep2" class="form-control" onfocus = 'use_number(this)' onblur ='use_text(this)'>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep3" class="label-form"> Motif Autre dépense 3</label>
                    <input type="text" name="MotifAutredep3" id="MotifAutredep3" class="form-control" onfocus="Somme()">
                </div>
                <div class="col">
                    <label for="Autredep3" class="label-form"> Montant </label>
                    <input type="text" name="Autredep3" id="Autredep3" class="form-control" >
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="TotalAutredep" class="label-form"> Total Montant Autre Dépense</label>
                    <input type="text" name="TotalAutredep" id="TotalAutredep" class="form-control">
                </div>
                <div class="col">
                    <label for="Alldepense" class="label-form"> Montant Total</label>
                    <input type="text" name="Alldepense" id="Alldepense" class="form-control">
                </div>
            </div>



            <div class="row">
                <div class="col">
                    <h4 style="text-align: center;">Mode de paiement</h4>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="modepaie" class="label-form"> Mode paiement</label>
                    <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme()">
                        <option value="ESPECES">ESPECES</option>
                        <option value="MOBILE MONEY">MOBILE MONEY</option>
                        <option value="VIREMENT BANCAIRE">VIREMENT BANCAIRE</option>
                    </select>
                </div>
                <div class="col">
                    <label for="modeesp" class="label-form" id="labelMode"> Mode</label>
                    <input type="text" name="valModesp" id="modeespece" class="form-control">
                    <?php foreach ($Compte as $Compte) : ?>
                        <input type="text" name="valModemob" id="modeMob" class="form-control" value="<?php echo $Compte['Numero_Telephone'] ?>">
                        <input type="text" name="valModecompt" id="modecompte" class="form-control" value="<?php echo $Compte['Numero_Compte_Bancaire'] ?>">
                    <?php endforeach ?>
                </div>
            </div>
            <div class="row">


            </div>
            <div class="row">
                <div class="mt-2 ">
                    <button type="submit" name="Envoyer" class="btn btn-info md-5" data-bs-toggle="tooltip"> <i class="fa fa-print"> Envoyer</i></button>
                </div>
            </div>

        </form>
    </div>
</body>

</html>