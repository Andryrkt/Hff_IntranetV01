<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col" style="text-align: right; color: #fbbb01; font-weight: bold;">
                        <h4>PROPOS DE VOUS</h4>
                    </div>
                </div>

                <div class="row">
                   
                        <div class="input-group input-group-sm mb-3">
                            <label for="email" class="input-group-text">Utilisateur:</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" name="email" value="<?php echo $UserConnect ?>">
                        </div>
                </div>
                <table class="table">
                            <thead class="table-dark">
                                <tr>
                                    <th style="text-align: center;">Profil</th>
                                    <th style="text-align: center;">Access App</th>
                                </tr>
                            </thead>
                            <?php foreach ($infoUserCours as $Info) : ?>
                            <tbody>
                                <td style="text-align: center;"><?php echo $Info['Profil'] ?></td>
                                <td style="text-align: center;"><?php echo $Info['App'] ?></td>
                            </tbody>
                            <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>