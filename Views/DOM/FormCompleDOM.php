<?php
include($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/DOM/FormPJ.php');
?>
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
            document.getElementById('labelMode01').innerHTML = "ESPECES";
        }
        if (mode === "MOBILE MONEY") {
            document.getElementById('modeMob').style.display = "block";
            document.getElementById('modeespece').style.display = "none";
            document.getElementById('modecompte').style.display = 'none';
            document.getElementById('labelMode').innerHTML = "MOBILE MONEY";
            document.getElementById('labelMode01').innerHTML = "MOBILE MONEY";
        }
        if (mode === "VIREMENT BANCAIRE") {
            document.getElementById('modeespece').style.display = "none";
            document.getElementById('modeMob').style.display = "none";
            document.getElementById('modecompte').style.display = "block";
            document.getElementById('labelMode').innerHTML = "VIREMENT BANCAIRE";
            document.getElementById('labelMode01').innerHTML = "VIREMENT BANCAIRE";
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
            var options = {
                style: 'decimal',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            };
        node.value = value.toLocaleString('en-US', options).replace(/,/g, '.');

    }

    function formatEtMettreAJour(champSource, champDestination) {
        // Récupérer la valeur actuelle du champ source
        let valeur = document.getElementById(champSource).value;

        // Supprimer tous les caractères non numériques
        valeur = valeur.replace(/[^\d]/g, '');

        // Convertir la chaîne en nombre
        let nombre = parseFloat(valeur);

        // Vérifier si le nombre est valide
        if (!isNaN(nombre)) {

            // Formater le nombre avec des séparateurs de milliers
            let valeurFormatee = nombre.toLocaleString('en-US').replace(/,/g, '.');

            // Mettre à jour le champ source avec le nombre formaté
            document.getElementById(champSource).value = valeurFormatee;

            // Mettre à jour le champ destination avec le nombre formaté
            document.getElementById(champDestination).value = valeurFormatee;

            // Appeler la fonction de somme
            sommeChamps('champ1', 'champ2');
        } else {
            // Si le nombre n'est pas valide, laisser les champs inchangés
            document.getElementById(champSource).value = '';
            document.getElementById(champDestination).value = '';
        }
    }

    function sommeEtIndemnite(champA, champB, champ2) {
        // Récupérer les valeurs des deux champs
        let valeurChampA = parseFloat(document.getElementById(champA).value.replace(/[^\d]/g, '')) || 0;
        let valeurChampB = document.getElementById(champB).value;

        // Calculer la somme
        let somme = valeurChampA * valeurChampB;

        // Formater la somme avec des séparateurs de milliers
        let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

        // Mettre à jour le champ2 avec la somme formatée
        document.getElementById(champ2).value = sommeFormatee;
    }

    function Somme() {
        var mont01 = document.getElementById('Autredep1').value;
        var mont02 = document.getElementById('Autredep2').value;
        var mont03 = document.getElementById('Autredep3').value;
        var montIndemTotal = document.getElementById('TotalidemForfait').value;
        var Smont01 = parseFloat(mont01.replace(/\./g, '').replace(',', ''));
        var Smont02 = parseFloat(mont02.replace(/\./g, '').replace(',', ''));
        var Smont03 = parseFloat(mont03.replace(/\./g, '').replace(',', ''));
        var SmontIndemTotal = parseFloat(montIndemTotal.replace(/\./g, '').replace(',', ''));
        if (mont01 === "") {
            Smont01 = 0
        }
        if (mont02 === "") {
            Smont02 = 0
        }
        if (mont03 === "") {
            Smont03 = 0
        }
        if (montIndemTotal === "") {
            SmontIndemTotal = 0
        }
        var options = {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        };
        var Somme = parseInt(Smont01, 10) + parseInt(Smont02) + parseInt(Smont03);
        var TotalAutre = document.getElementById('TotalAutredep')
        TotalAutre.value = Somme.toLocaleString('en-US', options).replace(/,/g, '.');

        STotalAutre = parseFloat(TotalAutre.value.replace(/\./g, '').replace(',', '.'));
        var SommeTo = parseInt(STotalAutre) + parseInt(SmontIndemTotal);
        var NetPaie = document.getElementById('Alldepense')
        NetPaie.value = SommeTo.toLocaleString('en-US', options).replace(/,/g, '.');

    }

    function calculerSomme(champA, champB, champC, TotalC) {
        // Récupérer les valeurs des deux champs
        let valeurChampA = parseFloat(document.getElementById(champA).value.replace('.', '')) || 0;
        let valeurChampB = parseFloat(document.getElementById(champB).value.replace('.', '')) || 0;
        let valeurChampC = parseFloat(document.getElementById(champC).value.replace('.', '')) || 0;
        // Calculer la somme
        let somme = valeurChampA + valeurChampB + valeurChampC;

        // Formater la somme avec des séparateurs de milliers
        let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

        // Mettre à jour le champ sommeTotal avec la somme formatée
        document.getElementById(TotalC).value = sommeFormatee;
    }

    function calculerSommeAll(champA, champB, TotalAll) {
        // Récupérer les valeurs des deux champs
        let valeurChampA = parseFloat(document.getElementById(champA).value.replace('.', '')) || 0;
        let valeurChampB = parseFloat(document.getElementById(champB).value.replace('.', '')) || 0;

        // Calculer la somme
        let somme = valeurChampA + valeurChampB;

        // Formater la somme avec des séparateurs de milliers
        let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

        // Mettre à jour le champ sommeTotal avec la somme formatée
        document.getElementById(TotalAll).value = sommeFormatee;
    }

    function Interne_externe() {
        var Interne = document.getElementById('Interne');
        var externe = document.getElementById('externe');
        var IntServ = document.getElementById('int');
        var ExtServ = document.getElementById('ext');
        var checkInterne = document.getElementById('radiochek').value;
        var OptInt = document.getElementById('OpInter');
        var OptExt = document.getElementById('OpExter');

        if (checkInterne === 'Interne') {
            externe.style.display = 'none';
            Interne.style.display = 'block'
            IntServ.style.display = 'block';
            ExtServ.style.display = 'none';
            OptInt.style.display = 'block';
            OptExt.style.display = 'none';
        } else {
            externe.style.display = 'block';
            Interne.style.display = 'none';
            IntServ.style.display = 'none';
            ExtServ.style.display = 'Block';
            OptInt.style.display = 'none';
            OptExt.style.display = 'Block';
        }
    }

    function Difference_date() {
        var DD = document.getElementById('dateDebut').value;
        var DF = document.getElementById('dateFin').value;
        var DateD = new Date(DD);
        var DateF = new Date(DF);
        if (DateD > DateF) {
            alert('Merci de vérifier la date précédente ');
        }
    }

    function typeCatge() {
        var catgRental = document.getElementById('MUTARENTAL');
        var catgSTD = document.getElementById('categ');
        var TypeMiss = document.getElementById('typeMission').value;
        var check = document.getElementById('radiochek').value;
        var codeservint = document.getElementById('ServINt').value;
        var codeservExt = document.getElementById('Serv').value;
        if (check === 'Interne') {
            codeSer = codeservint;
        } else {
            codeSer = codeservExt;
        }
        if (codeSer === '50 Rental' && TypeMiss == 'MUTATION') {
            catgRental.style.display = 'block';
            catgSTD.style.display = 'none';
        } else {
            catgRental.style.display = 'none';
            catgSTD.style.display = 'bloxk';
        }
    }
</script>

<body onload="visible_espece();Interne_externe(); typeCatge()"><!--/Hffintranet/Views/tcpdf/examples/Flight_brief_pdf.php-->
    <div class="container">
        <form action="/Hffintranet/index.php?action=EnvoyerImprime" method="POST" enctype="multipart/form-data" id="Formulaire">
            <div class="d-flex  flex-row-reverse  col">
                <button class="tablinks p-2 btn btn-outline-warning "> <a href="/Hffintranet/index.php?action=New_DOM" style="text-decoration: none;color:black">Retour</a></button>
            </div>
            <div class="row">
                <div class="col">
                    <label for="NumDOM" class="label-form">N° DOM</label>
                    <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $NumDom ?>" readonly>
                </div>
                <div class="col">
                    <label for="datesyst" class="label-form"> Date</label>
                    <input type="date" name="datesyst" id="datesyst" class="form-control" value="<?php echo $datesyst ?>" readonly>
                </div>
            </div>
            <div class="row" id="ext">
                <div class="col-4 offset-6">
                    <label for="Serv" class="label-form">Code :</label>
                    <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $code_service ?>" readonly>
                </div>
                <div class="col-4 offset-6">
                    <label for="LibServ" class="label-form">Service :</label>
                    <input type="text" name="LibServ" class="form-control" id="LibServ" value="<?php echo $service ?>" readonly>
                </div>
            </div>
            <div class="row" id="int">
                <?php foreach ($Compte as $Serv) : ?>
                    <div class="col-4 offset-6">
                        <label for="Serv" class="label-form">Code :</label>
                        <input type="text" name="ServINt" class="form-control" id="ServINt" value="<?php echo $Serv['Code_serv'] ?>" readonly>
                    </div>
                    <div class="col-4 offset-6">
                        <label for="LibServ" class="label-form">Service :</label>
                        <input type="text" name="LibServINT" class="form-control" id="LibServINT" value="<?php echo $Serv['Serv_lib'] ?>" readonly>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="typeMission" class="label-form"> Type de Mission</label>
                    <input name="typeMission" id="typeMission" class="form-control" value="<?php echo $typeMission ?>" readonly />
                </div>


                <!--<div class="col">
                    <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                    <input type="text" name="AutreType" class="form-control" id="AutreType" value="<?php echo $autrtype ?>">
                </div>-->
            </div>
            <div class="row">
                <div class="col" id="categ">
                    <label for="catego" class="label-form"> Catégorie:</label>
                    <input type="text" name="catego" id="catego" class="form-control" value="<?php echo $CategPers ?>">

                </div>
                <!---->
                <div class="col" id="MUTARENTAL"></div>
                <div class="col" id="SITE"></div>
                <!---->
            </div>

            <input type="hidden" name="radiochek" id="radiochek" value="<?php echo $check; ?>">
            <div class="row" id="Interne">
                <div class="col-6">
                    <label for="matricule" class="label-form"> Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="form-control" value="<?php echo $Maricule ?>">
                </div>
                <?php foreach ($Noms as $Noms) : ?>
                    <div class="col-6">
                        <label for="Nomprenoms" class="label-form"> Nom </label>
                        <input name="nomprenom" id="nomprenom" class="form-control" value="<?php echo $Noms['Nom'] ?>" />
                    </div>
                    <div class="col-6">
                        <label for="prenoms" class="label-form"> Prénoms </label>
                        <input name="prenom" id="prenom" class="form-control" value="<?php echo $Noms['Prenoms'] ?>" />
                    </div>
                <?php endforeach; ?>

            </div>
            <div class="row" id="externe">
                <div class="col">
                    <label for="namesExt" class="label-form"> Nom</label>
                    <input type="text" name="namesExt" id="namesExt" class="form-control" value="<?php echo $nomExt ?>" readonly>
                </div>
                <div class="col">
                    <label for="firstnamesExt" class="label-form"> Prénoms</label>
                    <input type="text" name="firstnamesExt" id="firstnamesExt" class="form-control" value="<?php echo $prenomExt ?>" readonly>
                </div>
                <div class="col">
                    <label for="cin" class="label-form"> CIN</label>
                    <input type="text" name="cin" id="cin" class="form-control" value="<?php echo $CINext ?>" readonly>
                </div>
            </div>


            <div class="row">
                <div class="col">
                    <label for="dateDebut" class="label-form"> Date début</label>
                    <input type="date" name="dateDebut" id="dateDebut" class="form-control" required style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="heureDebut" class="label-form"> Heure début</label>
                    <input type="time" name="heureDebut" id="heureDebut" class="form-control" required value="08:00" style="border-color: orange;">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="dateFin" class="label-form"> Date Fin</label>
                    <input type="date" name="dateFin" id="dateFin" class="form-control" onblur="recupeVal();Difference_date();sommeEtIndemnite('idemForfait','Nbjour','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense') " required style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="heureFin" class="label-form"> Heure Fin</label>
                    <input type="time" name="heureFin" id="heureFin" class="form-control" required value="18:00" style="border-color: orange;">

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="periode" class="label-form" id="nomprenom"> Nombre de Jour</label>
                    <input type="text" name="Nbjour" id="Nbjour" class="form-control" required style="text-align: right;" readonly>
                </div>

                <div class="col">
                    <label for="motif" class="label-form"> Motif</label>
                    <input type="text" name="motif" id="motif" class="form-control" required style="border-color: orange;">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="client" class="label-form"> Client</label>
                    <input type="text" name="client" id="client" class="form-control" style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="fiche" class="label-form"> N°fiche</label>
                    <input type="text" name="fiche" id="fiche" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="lieuInterv" class="label-form"> Lieu D'intervention</label>
                    <input type="text" name="lieuInterv" id="lieuInterv" class="form-control" required style="border-color: orange;">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="vehicule" class="label-form"> Véhicule Société</label>
                    <select name="vehicule" id="vehicule" class="form-select" style="border-color: orange;">
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
                    <label for="Devis" class="label-form">Devise:</label>

                    <select name="Devis" id="Devis" class="form-select">
                        <option value="MGA">MGA</option>
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="col">
                    <label for="idemForfait" class="label-form"> Indemnité Forfaitaire Journalière(s)</label>
                    <input type="text" name="idemForfait" id="idemForfait" class="form-control" oninput="formatEtMettreAJour('idemForfait', 'TotalidemForfait');" onblur="sommeEtIndemnite('idemForfait','Nbjour','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense') " required style="border-color: orange;" />
                    <input type="hidden" name="idemForfait01" id="idemForfait01" class="form-control" oninput="formatEtMettreAJour('idemForfait', 'TotalidemForfait');" onblur="sommeEtIndemnite('idemForfait','Nbjour','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense') " style="border-color: orange;" />
                </div>

                <div class="col">
                    <label for="TotalidemForfait" class="label-form"> Total d'Indemnité Forfaitaire</label>
                    <input type="text" name="TotalidemForfait" id="TotalidemForfait" class="form-control" readonly onblur='Somme();' />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep" class="label-form"> Motif Autre dépense 1</label>
                    <input type="text" name="MotifAutredep" id="MotifAutredep" class="form-control" style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="Autredep1" class="label-form"> Montant </label>
                    <input type="text" name="Autredep1" id="Autredep1" class="form-control" value="0" oninput="formatEtMettreAJour('Autredep1');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense')">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep2" class="label-form"> Motif Autre dépense 2</label>
                    <input type="text" name="MotifAutredep2" id="MotifAutredep2" class="form-control" style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="Autredep2" class="label-form"> Montant </label>
                    <input type="text" name="Autredep2" id="Autredep2" class="form-control" value="0" oninput="formatEtMettreAJour('Autredep2');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense')">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep3" class="label-form"> Motif Autre dépense 3</label>
                    <input type="text" name="MotifAutredep3" id="MotifAutredep3" class="form-control" style="border-color: orange;">
                </div>
                <div class="col">
                    <label for="Autredep3" class="label-form"> Montant </label>
                    <input type="text" name="Autredep3" id="Autredep3" class="form-control" value="0" oninput="formatEtMettreAJour('Autredep3');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep', 'Alldepense')">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="TotalAutredep" class="label-form"> Total Montant Autre Dépense</label>
                    <input type="text" name="TotalAutredep" id="TotalAutredep" class="form-control" oninput="formatEtMettreAJour('TotalAutredep');" readonly>
                </div>
                <div class="col">
                    <label for="Alldepense" class="label-form"> Montant Total</label>
                    <input type="text" name="Alldepense" id="Alldepense" class="form-control" oninput="formatEtMettreAJour('Alldepense');" readonly>
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
                    <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme(); Interne_externe()" style="border-color: orange;">
                        <option value="MOBILE MONEY">MOBILE MONEY</option>
                        <option value="ESPECES">ESPECES</option>
                        <option value="VIREMENT BANCAIRE">VIREMENT BANCAIRE</option>
                    </select>
                </div>
                <div class="col" id="OpInter">
                    <label for="modeesp" class="label-form" id="labelMode"> Mode</label>
                    <input type="text" name="valModesp" id="modeespece" class="form-control">
                    <?php foreach ($Compte as $Num) : ?>
                        <input type="text" name="valModemob" id="modeMob" class="form-control" value="<?php echo $Num['NumeroTel_Recente'] ?>" style="border-color: orange;">
                        <input type="text" name="valModecompt" id="modecompte" class="form-control" value="<?php echo $Num['Numero_Compte_Bancaire'] ?>">
                    <?php endforeach; ?>
                </div>
                <div class="col" id="OpExter">
                    <label for="modeesp" class="label-form" id="labelMode01"> Mode</label>
                    <input type="text" name="valModespExt" id="valModespExt" class="form-control">
                </div>
            </div>

            <div class="row" id="label_PJ">
                <div class="col">
                    <h4 style="text-align: center;">Pièce Jointe</h4>
                </div>
            </div>
            <div class="row" id="PJ">
                <div class="col">
                    <label for="file01" class="label-form"> Fichier joint 01 (Merci de mettre un fichier PDF):</label>
                    <?php
                    inputFields("", "file01", "file01", "", "file");
                    ?>
                </div>
                <div class="col">
                    <label for="file02" class="label-form"> Fichier joint 02 (Merci de mettre un fichier PDF):</label>
                    <?php
                    inputFields("", "file02", "file02", "", "file");
                    ?>
                </div>

            </div>
            <div class="row">
                <div class="mt-2 ">
                    <a onclick="return confirm('Voulez-vous envoyer la demande ?')"> <button type="submit" name="Envoyer" class="btn btn-info md-5" data-bs-toggle="tooltip"> <i class="fa fa-print"> Envoyer</i></button> </a>
                </div>
            </div>

        </form>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        // Fonction pour gérer le changement du champ ServINt
        function handleServINtChange() {
            var valeurCode = $('#ServINt').val();
            var typeMission = $('#typeMission').val();
            var codeServ = valeurCode.substring(0, 2);
            if (typeMission === "MUTATION" && codeServ === '50') {
                $.ajax({
                    type: 'POST',
                    url: '/Hffintranet/index.php?action=SelectCatgeRental',
                    data: {
                        CodeRental: codeServ
                    },
                    success: function(response) {
                        $('#MUTARENTAL').html(response).show();
                        handleSiteRental();
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            } else {
                $('#MUTARENTAL').hide();
            }

        }

        function handleSiteRental() {
            var MutatRental = $('#MUTARENTAL option:selected').text();
            MutaRental = MutatRental.replace(/\+/g, ' '); //categorie select
            var catgePErs = $('#catego').val(); //no rental
            CatgePers = catgePErs.replace(/\+/g, ' ');
            var valeurCode = $('#ServINt').val();
            var typeMission = $('#typeMission').val();
            var codeServ = valeurCode.substring(0, 2);
            if (MutaRental.trim() !== "") {

                if (typeMission === "MUTATION" && codeServ === '50') {
                    $.ajax({
                        type: 'POST',
                        url: '/Hffintranet/index.php?action=selectIdem',
                        data: {
                            CategPers: MutaRental,
                            TypeMiss: typeMission

                        },
                        success: function(response1) {
                            $('#SITE').html(response1).show();
                            handlePrixRental();
                        },
                        error: function(error) {
                            console.error(error);
                        }
                    });
                }
            }

            if (typeMission === "MUTATION" && codeServ !== '50') {
                $.ajax({
                    type: 'POST',
                    url: '/Hffintranet/index.php?action=selectIdem',
                    data: {
                        CategPers: CatgePers,
                        TypeMiss: typeMission
                    },
                    success: function(response1) {
                        $('#SITE').html(response1).show();
                        handlePrixRental();
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }
            if (typeMission === "MISSION") {


                $.ajax({
                    type: 'POST',
                    url: '/Hffintranet/index.php?action=selectIdem',
                    data: {
                        CategPers: CatgePers,
                        TypeMiss: typeMission
                    },
                    success: function(response1) {
                        $('#SITE').html(response1).show();
                        handlePrixRental();
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }

        }

        function handlePrixRental() {
            var SiteRental = $('#SITE option:selected').text();
            SiteRental01 = SiteRental.replace(/\+/g, ' ');
            var MutatRental = $('#MUTARENTAL option:selected').text();
            MutaRental = MutatRental.replace(/\+/g, ' ');
            var valeurCode = $('#ServINt').val();
            var typeMission = $('#typeMission').val();
            var codeServ = valeurCode.substring(0, 2);
            if (SiteRental01.trim() !== "") {

                if (typeMission === "MUTATION" && codeServ === '50') {
                    $.ajax({
                        type: 'POST',
                        url: '/Hffintranet/index.php?action=SelectPrixRental',
                        data: {
                            typeMiss: typeMission,
                            categ: MutaRental,
                            siteselect: SiteRental01,
                            codeser: codeServ
                        },
                        success: function(PrixRental) {
                            $('#idemForfait').val(PrixRental).show();
                        },
                        error: function(error) {
                            console.error(error);
                        }
                    });
                }
            }
            if (typeMission === "MUTATION" && codeServ !== '50') {
                var catgePErs = $('#catego').val();
                CatgePers = catgePErs.replace(/\+/g, ' ');
                $.ajax({
                    type: 'POST',
                    url: '/Hffintranet/index.php?action=SelectPrixRental',
                    data: {
                        typeMiss: typeMission,
                        categ: CatgePers,
                        siteselect: SiteRental01,
                        codeser: codeServ
                    },
                    success: function(PrixRental) {
                        $('#idemForfait').val(PrixRental).show();
                        $('#idemForfait01').val(PrixRental).show();
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }
            if (typeMission === "MISSION") {
                var catgePErs = $('#catego').val();
                CatgePers = catgePErs.replace(/\+/g, ' ');
                $.ajax({
                    type: 'POST',
                    url: '/Hffintranet/index.php?action=SelectPrixRental',
                    data: {
                        typeMiss: typeMission,
                        categ: CatgePers,
                        siteselect: SiteRental01,
                        codeser: codeServ
                    },
                    success: function(PrixRental) {
                        $('#idemForfait').val(PrixRental).show();
                        $('#idemForfait01').val(PrixRental).show();
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }


        }


        // Gérer le changement du champ ServINt lors du chargement de la page et lors de la saisie
        $('#ServINt').on('input', function() {
            handleServINtChange();
        });

        $('#MUTARENTAL').change(function() {
            handleSiteRental();
        });
        $('#SITE').change(function() {
            handlePrixRental();
        });
        handleServINtChange();
        handleSiteRental();
        handlePrixRental();
    });
</script>

</html>