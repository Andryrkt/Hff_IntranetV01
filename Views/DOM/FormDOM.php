<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff intranet</title>

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

    function Matricule() {
        var names = document.getElementById('nomprenom').value;
        let result = names.substring(names.length - 4);
        document.getElementById('matricule').value = result;
    }

    function Interne_externe() {
        var Interne = document.getElementById('Interne');
        var externe = document.getElementById('externe');

        var checkInterne = document.getElementById('radiochek').value;
        if (checkInterne === 'Interne') {
            externe.style.display = 'none';
            Interne.style.display = 'block'
        } else {
            externe.style.display = 'block';
            Interne.style.display = 'none';
        }
    }
</script>
<?php
$fichier = $_SERVER['DOCUMENT_ROOT'] . 'Hff_IntranetV01/Views/Acces/Agence.txt';

foreach ($CodeServiceofCours as $code) :
    $LibAgence = $code['nom_agence_i100'];
    $LibServ = $code['nom_service_i100'];
endforeach;
$Agence = $LibAgence . " " . $LibServ;

?>
<style>
    #chek {
        <?php
        if (strpos(file_get_contents($fichier), $Agence) !== false) {

        } else {
            echo 'display: none';
        }
        ?>
    }
</style>

<body onload="visible(); Matricule();Interne_externe()">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="col" style="text-align: right;font-weight: bold;">
                    <h4>Demande d'Orde de Mission</h4>
                </div>
                <form method="POST" action="/Hff_IntranetV01/index.php?action=checkMatricule">
                    <div class="row">
                        <div class="col">
                            <label for="NumDOM" class="label-form">N° DOM</label>
                            <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $NumDOM ?>" readonly>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col">
                            <?php foreach ($CodeServiceofCours as $CodeServiceofCours) : ?>
                                <label for="Serv" class="label-form">Code :</label>
                                <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $CodeServiceofCours['agence_ips'] . " " . iconv('Windows-1252', 'UTF-8',$CodeServiceofCours['nom_agence_i100']) ?>" readonly><!--echo iconv('Windows-1252', 'UTF-8', $observe)-->
                        </div>
                        <div class="col">
                            <label for="LibServ" class="label-form">Service :</label>
                            <input type="text" name="LibServ" class="form-control" id="LibServ" value="<?php echo $CodeServiceofCours['service_ips'] . " " . $CodeServiceofCours['nom_service_i100'] ?>" readonly>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="typeMission" class="label-form"> Type de Mission</label>
                            <select name="typeMission" id="typeMission" class="form-select" onchange="visible()"><!--à discuter voir dans le fiche -->
                                <?php foreach ($TypeDocument as $TypeDocument) : ?>
                                    <option value="<?php echo $TypeDocument['Code_Sous_type'] ?>"><?php echo $TypeDocument['Code_Sous_type'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                            <input type="text" name="AutreType" class="form-control" id="AutreType">
                        </div>
                    </div>
                    <div class="row" id="chek">

                        <div class="col-4">
                            <label for="AutreType" class="label-form" id="labAutre"> Salarié:</label>
                            <select name="radiochek" id="radiochek" class="form-select "  onchange="Interne_externe()">
                                <option value="Interne">Permanent</option>
                                <option value="Externe">Temporaire</option>
                            </select>
                        </div>

                    </div>
                    <div class="row" id="Interne">
                        <div class="col">
                            <label for="Nomprenoms" class="label-form"> Nom - Matricule</label>
                            <select name="nomprenom" id="nomprenom" class="form-control" onchange="Matricule()" onblur="envoyerDonnees()">
                                <?php foreach ($PersonelServOfCours as $PersonelServOfCours) : ?>
                                    <option value="<?php echo $PersonelServOfCours['Nom'] . " - " . $PersonelServOfCours['Matricule'] ?>"> <?php echo $PersonelServOfCours['Nom'] . " - " . $PersonelServOfCours['Matricule'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="matricule" class="label-form"> Matricule</label>
                            <input type="text" name="matricule" id="matricule" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row" id="externe">
                        <div class="col">
                            <label for="namesExt" class="label-form"> Nom</label>
                            <input type="text" name="namesExt" id="namesExt" class="form-control">
                        </div>
                        <div class="col">
                            <label for="firstnamesExt" class="label-form"> Prénoms</label>
                            <input type="text" name="firstnamesExt" id="firstnamesExt" class="form-control">
                        </div>
                        <div class="col">
                            <label for="cin" class="label-form"> CIN</label>
                            <input type="text" name="cin" id="cin" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-2">
                            <button type="submit" class="btn btn-info"> Suivant</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>

</html>