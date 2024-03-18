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
                    //$('#idemForfait01').val(PrixRental).show();
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }


    }

    function verifiationTYpeMission() {
        var TypeMission = $('#typeMission').val();
        var idemite_jour = $('#idemForfait');
        var TelMobile = $('#modeMob');
        if (TypeMission === "FRAIS EXCEPTIONNEL") {
            idemite_jour.prop("readonly", false);
        } else {
            idemite_jour.prop("readonly", true);
        }
        if (TypeMission === "FRAIS EXCEPTIONNEL") {
            TelMobile.prop("required", false);
        } else {
            TelMobile.prop("required", true);
        }
    }

    function MobileMoney() {
        var TelMobileval = $('#modeMob').val();
        var TelMobile = $('#modeMob');
        var typeMode = $('#modepaie option:selected').val();
        var check = $('#radiochek').val();


        if (typeMode !== 'MOBILE MONEY') {
            TelMobile.prop("required", false);
        } else if (typeMode === 'MOBILE MONEY' && (TelMobileval === undefined || TelMobileval.trim() === '') && check === 'Interne') {
            TelMobile.prop("required", true);
        } else {
            TelMobile.prop("required", false);
        }

    }

    function FicheAtelier() {
        var check = $('#radiochek').val();
        var libservINT = $('#LibServINT').val();
        var libservEXT = $('#LibServ').val();
        var fiche = $('#fiche');

        var servlib;

        if (check === 'Interne') {
            servlib = libservINT.substring(0, 3);
        } else {
            servlib = libservEXT.substring(0, 3);
        }

        var valService = ['MAS', 'ATE', 'CSP'];
        var motTrouver = valService.some(function(mot) {
            return servlib.includes(mot);
        });

        fiche.prop("required", motTrouver);
        console.log(servlib);
    }


    $('#ServINt').on('input', function() {
        handleServINtChange();
    });

    $('#MUTARENTAL').change(function() {
        handleSiteRental();
    });
    $('#SITE').change(function() {
        handlePrixRental();
    });

    $('#modepaie').change(function() {
        MobileMoney();
    });

    handleServINtChange();
    handleSiteRental();
    handlePrixRental();
    verifiationTYpeMission();
    MobileMoney();
    FicheAtelier();
});

// DEBUT javascript pour selecte debiteur


function Select1Value(data, selectedOption) {
    const select1 = document.querySelector('#select1');

    // Vider le contenu de l'élément select
    select1.innerHTML = '';

    // Ajouter les options
    for (const key in data) {
        select1.innerHTML += `<option value="${key}">${key}</option>`;
    }

    // Sélectionner l'option spécifiée
    if (selectedOption !== undefined) {
        select1.value = selectedOption;
    }
}

function fetchData(selectOption = undefined) {
    const url = "/Hffintranet/index.php?action=anaranaaction";
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur de réseau');
            }
            return response.json();
        })
        .then(data => {
            Select1Value(data, selectOption);
            // Sélectionner l'option spécifiée
            if (selectOption === undefined) {
                setTimeout(() => {
                    selectOption = document.getElementById('select1').value;
                    console.log(selectOption);
                }, 300);

            }


            setTimeout(() => {
                console.log(selectOption);
                const serviceIriumElement = document.getElementById('serviceIrium');
                let taille = data[selectOption].length;
                console.log(taille);
                let optionsHTML = ''; // Chaîne pour stocker les options HTML
                for (let i = 0; i < taille; i++) {
                    optionsHTML += `<option value="${data[selectOption][i]}">${data[selectOption][i]}</option>`;
                }
                serviceIriumElement.innerHTML = optionsHTML;
            }, 300); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
        })
        .catch(error => {
            console.error(error);
        });
}

// Appel initial de fetchData sans argument
fetchData();

document.getElementById('select1').addEventListener('change', function() {
    var selectedOption = this.value;
    fetchData(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
});

let check = document.getElementById('radiochek').value;
if (check === "Interne") {
    setTimeout(() => {
        document.querySelector(`#select1 option[value="${document.querySelector('#ServINt').value}"]`).selected = true;
    }, 300);
} else {
    setTimeout(() => {
        document.querySelector(`#select1 option[value="${document.querySelector('#Serv').value}"]`).selected = true;
    }, 300);
}


if (check === "Interne") {
    setTimeout(() => {
        document.querySelector(`#serviceIrium option[value="${document.querySelector('#LibServINT').value.toUpperCase()}"]`).selected = true;
    }, 500);

} else {
    setTimeout(() => {
        console.log(document.querySelector('#LibServ').value.toUpperCase());
        document.querySelector(`#serviceIrium option[value="${document.querySelector('#LibServ').value.toUpperCase()}"]`).selected = true;
    }, 500);

}


//FIN Javascript pour le débitteur select
