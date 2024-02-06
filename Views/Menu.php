<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff Intranetv01</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<style>
    .nav-item:hover{
        color: #fbbb01;
    }
    @media screen and (max-width : 1356px) {
        #LogoHFF {
            width: 50%;
        }
        #collapsibleNavbar{
           
        }
    }
</style>

<body>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark" style="margin-bottom: 2%;">
        <div class="container-fluid">
            <a class="navbar-brand" href="/Hffintranet/index.php?action=#"><img src="/Hffintranet/Views/assets/logoHFF.jpg" id="LogoHFF" alt="" width="150px"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse " id="collapsibleNavbar" >
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Demande d'Ordre de Mission</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Demande d'Intervention</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Demande d'Appro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Support Info</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color: #fbbb01;"><?php echo $UserConnect ?> </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/Hffintranet/index.php?action=Propos">Propos de vous</a></li>
                            <li><a class="dropdown-item" href="/Hffintranet/index.php?action=Logout">déconnection</a></li>
                        </ul>
                    </li>
                    
                </ul>
            </div>
            
        </div>


    </nav>

</body>

</html>