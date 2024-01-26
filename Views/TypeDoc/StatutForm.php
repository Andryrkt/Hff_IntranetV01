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
                <h3 style="text-align: center;">Type de Document</h3>
                <form action="/Hff_IntranetV01/index.php?action=#" method="POST">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="CodeApp"> Application</label>
                        <div class="col-sm-10">
                            <!-- <input class="form-control" id="focusedInput" type="text" value="Click to focus...">-->
                            <input type="text" class="form-control" name="CodeApp" id="CodeApp">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="CodeStatut">Sous Type</label>
                        <div class="col-sm-10">
                            <input class="form-control" id="CodeStatut" type="text" name="CodeStatut">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="descStatut">Description</label>
                        <div class="col-sm-10">
                            <input class="form-control" id="descStatut" type="text" name="descStatut">
                        </div>
                    </div>
                    <div class="btn-group" style="margin-top: 1%;">
                        <button name="btn_coms" type="submit" class="btn btn-success" value="ADD">Ajouter</button>
                        <!---  <a href="/Hff_IntranetV01/index.php?action=Acceuil " class="btn btn-danger"> Annuler</a>-->
                    </div>

                </form>
            </div>
            <div class="col">
                <table class=" table">
                    <thead class="table-dark">
                        <tr>
                            <th style="text-align: center;">Code App </th>
                            <th style="text-align: center;">Code Statut</th>
                            <th style="text-align: center;">Description</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</body>

</html>