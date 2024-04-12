


function Select1Value(data, selectedOption) {
    const select1 = document.querySelector('#select1');

    // Vider le contenu de l'élément select
    select1.innerHTML = '';

    // Ajouter les options
    for (const key in data) {
        select1.innerHTML += `<option value="${key.toUpperCase()}">${key.toUpperCase()}</option>`;
    }

    // Sélectionner l'option spécifiée
    if (selectedOption !== undefined) {
        select1.value = selectedOption.toUpperCase();
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

            console.log(data);
            Select1Value(data, selectOption);
            //Sélectionner l'option spécifiée
            if (selectOption === undefined) {
                setTimeout(() => {
                    selectOption = document.getElementById('select1').value.toUpperCase();
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
                    optionsHTML += `<option value="${data[selectOption][i].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
                }
                serviceIriumElement.innerHTML = optionsHTML;
            }, 300); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
        })
        .catch(error => {
            console.error(error);
        });
}

//DEBUT Duplication




console.log(document.querySelector('#NumDOM'));

    // Vérifier si les données sont déjà disponibles
if ( document.querySelector('#NumDOM') === null  ) {
       // DEBUT javascript pour selecte debiteur


    // Appel initial de fetchData sans argument
    fetchData();

    document.getElementById('select1').addEventListener('change', function() {
        var selectedOption = this.value.toUpperCase();
        fetchData(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
    });



    let check = document.getElementById('radiochek').value;
    if (check === "Interne") {
        setTimeout(() => {
            console.log( document.querySelector(`#select1 option`));
            document.querySelector(`#select1 option[value="${document.querySelector('#ServINt').value.toUpperCase()}"]`).selected = true;
        }, 500);
        setTimeout(() => {
            console.log('voici :'+ document.querySelector('#LibServINT').value.toUpperCase());
            libserv = document.querySelector('#LibServINT').value.toUpperCase().trim();
             document.querySelector(`#serviceIrium option[value="${document.querySelector('#LibServINT').value.toUpperCase().trim()}"]`).selected = true;
        }, 1000);
    } else {
        setTimeout(() => {
             const serv = document.querySelector('#Serv').value.toUpperCase().trim();
             console.log(serv);
            console.log(document.querySelector(`#select1 option[value="${serv}"]`));
            document.querySelector(`#select1 option[value="${serv}"]`).selected = true;
        }, 500);
        setTimeout(() => {
            console.log(document.querySelector('#LibServ').value.toUpperCase().trim());
            document.querySelector(`#serviceIrium option[value="${document.querySelector('#LibServ').value.toUpperCase()}"]`).selected = true;
        }, 1000);
    }

console.log(check);
   
//FIN Javascript pour le débitteur select
} else {

        function fetchDataDuplier() {
            const url = "/Hffintranet/index.php?action=Dupliquer";
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur de réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    //console.log(data);
                    $donnerFiltrer = filtre(data);
                    console.log($donnerFiltrer);

                console.log($donnerFiltrer[0].Debiteur.toUpperCase());
                  console.log($donnerFiltrer[0].Debiteur.split('-')[0].toUpperCase());
                  console.log($donnerFiltrer[0].Debiteur.split('-')[1].toUpperCase());
        
                  console.log($donnerFiltrer[0].Site.toUpperCase());
                  
                  setTimeout(() => {
                    if ($donnerFiltrer[0].Debiteur.split('-')[0].toUpperCase() === '60 PNEU ') {
                        document.querySelector(`#select1 option[value="60 PNEU - OUTIL - LUB"]`).selected = true;
                    } else {
                        document.querySelector(`#select1 option[value="${$donnerFiltrer[0].Debiteur.split('-')[0].toUpperCase()}"]`).selected = true;
                    }
                    
                    
                    document.querySelector(`#SITE option[value="${$donnerFiltrer[0].Site.toUpperCase()}"]`).selected = true
                }, 200);


                
                
                // Appel initial de fetchData sans argument
                fetchData();
                
                document.getElementById('select1').addEventListener('change', function() {
                    var selectedOption = this.value.toUpperCase();
                    fetchData(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
                });


                setTimeout(() => {
                    if ($donnerFiltrer[0].Debiteur.split('-')[1] === ' OUTIL ') {
                        document.querySelector(`#serviceIrium option[value="${$donnerFiltrer[0].Debiteur.split('-')[3]}"]`).selected = true;
                        
                    } else {
                        
                        document.querySelector(`#serviceIrium option[value="${$donnerFiltrer[0].Debiteur.split('-')[1]}"]`).selected = true;
                    }
            }, 500);
                })
                .catch(error => {
                    console.error(error);
                });
        }


        fetchDataDuplier();

        // document.getElementById('select1').addEventListener('change', function() {
        //     var selectedOption = this.value.toUpperCase();
        //     fetchDataDuplier(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
        // });

        function filtre(data) {
            // Récupérer les valeurs des champs de saisie
           
            numDom = document.querySelector('#NumDOM').value;
            idDom = document.querySelector('#IdDOM').value;
            
              
            // Filtrer les données en fonction des critères
            return resultatsFiltres = data.filter(function(demande) {
            
                var filtreNumDom = demande.Numero_Ordre_Mission === numDom
                var filtreIdDom = demande.ID_Demande_Ordre_Mission  === idDom
                 
                // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
                return (filtreNumDom && filtreIdDom);
            });
        }
    }






//FIN Duplication












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