
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Hff Intranet</title>
    <link href="/Hff_IntranetV01/Views/css/styles.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Hff_IntranetV01/Views/js/scripts.js"></script>
</head>
<style>
    #Setting {
        <?php
        $fichier = "../Hff_IntranetV01/Views/assets/AccessUserProfil.txt";
        if ((file_exists($fichier)) && (is_readable($fichier))) {
            $text = file_get_contents($fichier);
           //echo $text;
            if (strpos($text, $_SESSION['user']) !== false) {
                echo 'display:block';
            } else {
                echo 'display:none';
            }
        } else {
            echo 'Le fichier ' . $fichier . ' n\'existe pas ou n\'est pas disponible en ouverture';
        }
        ?>
    }
</style>
<body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 1%;">
        <div class="container">
            <a class="navbar-brand" href="/Hff_IntranetV01/index.php?action=Acceuil"><img src="/Hff_IntranetV01/Views/assets/logoHFF.jpg" id="LogoHFF" alt="" width="150px"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent" style="color: #fbbb01;">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Demande d'Ordre de Mission</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=New_DOM">Nouvelle Demande d'Ordre</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Ordre</a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Demande d'Intervention</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=#">Nouvelle Demande d'Intervention</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Intervention </a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Demande d'Appro</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=#">Nouvelle Demande d'Appro</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Appro</a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Support Info</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=#">Nouveau Support</a></li>
                            <li><a class="dropdown-item" href="#">Liste de Support </a></li>

                        </ul>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fbbb01;"><?php echo $UserConnect ?></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Mes accès</a></li>
                            <li><a class="dropdown-item" href="#" id="Setting">paramètre</a></li>
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Personnels" id="Perso">Personnels</a></li>
                            <li><a class="dropdown-item" href="#" id="AgServ">Agence-Service </a></li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Logout">Déconnection</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


</body>

</html>