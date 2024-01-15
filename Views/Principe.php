<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Hff Intranet</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="/Hff_IntranetV01/Views/css/styles.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="/Hff_IntranetV01/Views/js/scripts.js"></script>
</head>

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
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Nouvelle Demande d'Ordre</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Ordre</a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Demande d'Intervention</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Nouvelle Demande d'Intervention</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Intervention </a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Demande d'Appro</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Nouvelle Demande d'Appro</a></li>
                            <li><a class="dropdown-item" href="#">Liste des demandes d'Appro</a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Support Info</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Nouveau Support</a></li>
                            <li><a class="dropdown-item" href="#">Liste de Support </a></li>

                        </ul>
                    </li>


                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fbbb01;"><?php echo $UserConnect ?></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/Hff_IntranetV01/index.php?action=Propos">Propos</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
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
    <div class="container">
        <div id="demo" class="carousel slide" data-bs-ride="carousel">

            <!-- Indicators/dots -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
            </div>

            <!-- The slideshow/carousel -->
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="/Hff_IntranetV01/Views/assets/PIC03.jpg" alt="Los Angeles" class="d-block" style="width:100%" height="auto">
                </div>
                <div class="carousel-item">
                    <img src="/Hff_IntranetV01/Views/assets/PIC01.jpg" alt="Chicago" class="d-block" style="width:100%">
                </div>
                <div class="carousel-item">
                    <img src="/Hff_IntranetV01/Views/assets/PIC02.jpg" alt="New York" class="d-block" style="width:100%">
                </div>
                <div class="carousel-item">
                    <img src="/Hff_IntranetV01/Views/assets/PIC04.jpg" alt="chine" class="d-block" style="width:100%">
                </div>
            </div>

            <!-- Left and right controls/icons -->
            <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        

    </div>

</body>

</html>