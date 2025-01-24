<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff Intranet</title>
</head>

<body>
    <div class="container">
        <div class="row" style="margin-top: 1%;">
            <div class="col">
                <h3 style="text-align: center;">Agence Service Autoriser</h3>
                <form action="/Hffintranet/index.php?action=MoveTypeDoc" method="POST">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="User"> Utilisateur</label>
                        <div class="col-sm-10">
                            <!-- <input class="form-control" id="focusedInput" type="text" value="Click to focus...">-->
                            <input type="text" class="form-control" name="User" id="User">
                        </div>
                    </div>
                    <div class="form-group" id="AgenceAll">

                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="CodeAgence">Code</label>
                        <div class="col-sm-10">
                            <input class="form-control" id="CodeAgence" type="text" name="CodeAgence" readonly>
                        </div>
                    </div>
                    <div class="btn-group" style="margin-top: 1%;">
                        <button name="btn_coms" type="submit" class="btn btn-success" value="ADD">Ajouter</button>
                        <!---  <a href="/Hffintranet/index.php?action=Acceuil " class="btn btn-danger"> Annuler</a>-->
                    </div>

                </form>
            </div>
            <div class="col table-responsive" style="height: 510px;">
                <table class=" table ">
                    <thead class="table-dark">
                        <tr>
                            <th></th>
                            <th style="text-align: center;">Utilisateur </th>
                            <th style="text-align: center;">AgenceService_Autoriser</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ListAgenceAuto as $ListAgenceAuto) : ?>
                            <tr>
                                <td>
                                    <a onclick="return confirm('Vous êtes sûr de supprimer !')" class="btn btn-danger" href="/Hffintranet/index.php?action=DelAgAuto&Id=<?php echo $ListAgenceAuto['ID_Agence_Service_Autorise'] ?>"> Supprimer</a>
                                </td>
                                <td> <?php echo $ListAgenceAuto['Session_Utilisateur'] ?></td>
                                <td> <?php echo $ListAgenceAuto['Code_AgenceService_IRIUM'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        // Fonction pour gérer le changement du champ ServINt
        function ShowAgenceAll() {
            $.ajax({
                type: 'POST',
                url: '/Hffintranet/index.php?action=AgenceServiceAutoAll',
                success: function(response) {
                    $('#AgenceAll').html(response).show();
                    ShowCodeAgence();
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }

        function ShowCodeAgence() {
            var LibAgence = $('#AgenceAll option:selected').text();
            $.ajax({
                type: 'POST',
                url: '/Hffintranet/index.php?action=CodeAgenceServiceAuto',
                data: {
                    libAgServ: LibAgence
                },
                success: function(CodeAgServ) {
                    $('#CodeAgence').val(CodeAgServ).show();

                },
                error: function(error) {
                    console.error(error);
                }
            });
        }
        $('#AgenceAll').change(function() {
            ShowCodeAgence();
        });
        ShowAgenceAll()
        ShowCodeAgence()
    });
</script>

</html>