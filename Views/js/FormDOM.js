


function Matricule() {
    var names = document.getElementById('nomprenom').value;
    let result = names.substring(0, 4);
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


window.addEventListener('load', function() {
    Matricule();
    Interne_externe();
});




$(document).ready(function() {
    $('#typeMission').change(function() {
        var valeurSelectionnee = $(this).val();
        var Agence = $('#Serv').val();
        var codeAgence = Agence.substring(0, 2);
        if (valeurSelectionnee === "MISSION" || valeurSelectionnee === "MUTATION") {
            $.ajax({
                type: 'POST',
                url: '/Hffintranet/index.php?action=SelectCateg',
                data: {
                    typeMission: valeurSelectionnee,
                    CodeAg: codeAgence
                },
                success: function(response) {
                    if (response.trim() === "") {
                        $('#affichage_container').hide();
                    } else {
                        $('#affichage_container').html(response).show();
                    }
                },
                error: function(error) {
                    console.error(error);
                }
            });
        } else {
            $('#affichage_container').hide();
        }
    });
    $('#typeMission').change();
});