

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

function recupeVal() {
    var DateD = document.getElementById('dateDebut').value;
    var DateF = document.getElementById('dateFin').value;

    var StartDate = new Date(DateD);
    var EndDate = new Date(DateF);
    var DiffDate = (EndDate - StartDate) / (1000 * 60 * 60 * 24) + 1;
    document.getElementById('Nbjour').value = DiffDate;
}

function visible_espece() {
    var mode = document.getElementById('modepaie').value;
    if (mode === "ESPECES") {
        document.getElementById('modeMob').style.display = "none";
        document.getElementById('modecompte').style.display = "none";
        document.getElementById('modeespece').style.display = "block";
        document.getElementById('labelMode').innerHTML = "ESPECES";
        document.getElementById('labelMode01').innerHTML = "ESPECES";
    }
    if (mode === "MOBILE MONEY") {
        document.getElementById('modeMob').style.display = "block";
        document.getElementById('modeespece').style.display = "none";
        document.getElementById('modecompte').style.display = 'none';
        document.getElementById('labelMode').innerHTML = "MOBILE MONEY";
        document.getElementById('labelMode01').innerHTML = "MOBILE MONEY";
    }
    if (mode === "VIREMENT BANCAIRE") {
        document.getElementById('modeespece').style.display = "none";
        document.getElementById('modeMob').style.display = "none";
        document.getElementById('modecompte').style.display = "block";
        document.getElementById('labelMode').innerHTML = "VIREMENT BANCAIRE";
        document.getElementById('labelMode01').innerHTML = "VIREMENT BANCAIRE";
    }

}

function indemnité() {
    var idemn = document.getElementById('idemForfait').value;
    var nbjour = document.getElementById('Nbjour').value;

    var total = idemn * nbjour
    document.getElementById('TotalidemForfait').value = total;
}

function use_number(node) {
    var empty_val = false;
    const value = node.value;
    if (node.value == '')
        empty_val = true;
    node.type = 'number';
    /* if (!empty_val)
         node.value = Number(value.replace(/,/g, '')); */
}

function use_text(node) {
    var empty_val = false;
    const value = Number(node.value);
    if (node.value == '')
        empty_val = true;
    node.type = 'text';
    if (!empty_val)
        var options = {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        };
    node.value = value.toLocaleString('en-US', options).replace(/,/g, '.');

}

function formatEtMettreAJour(champSource, champDestination) {
    // Récupérer la valeur actuelle du champ source
    let valeur = document.getElementById(champSource).value;

    // Supprimer tous les caractères non numériques
    valeur = valeur.replace(/[^\d]/g, '');

    // Convertir la chaîne en nombre
    let nombre = parseFloat(valeur);

    // Vérifier si le nombre est valide
    if (!isNaN(nombre)) {

        // Formater le nombre avec des séparateurs de milliers
        let valeurFormatee = nombre.toLocaleString('en-US').replace(/,/g, '.');

        // Mettre à jour le champ source avec le nombre formaté
        document.getElementById(champSource).value = valeurFormatee;

        // Mettre à jour le champ destination avec le nombre formaté
        document.getElementById(champDestination).value = valeurFormatee;

        // Appeler la fonction de somme
        sommeChamps('champ1', 'champ2');
    } else {
        // Si le nombre n'est pas valide, laisser les champs inchangés
        document.getElementById(champSource).value = '';
        document.getElementById(champDestination).value = '';
    }
}

function sommeEtIndemnite(champA, champB, champC, champ2) {
    // Récupérer les valeurs des deux champs
    let valeurChampA = parseFloat(document.getElementById(champA).value.replace(/[^\d]/g, '')) || 0;
    let valeurChampC = parseFloat(document.getElementById(champC).value.replace(/[^\d]/g, '')) || 0;
    let valeurChampB = document.getElementById(champB).value;


    // Calculer la somme
    let somme = (valeurChampA + valeurChampC) * valeurChampB;

    // Formater la somme avec des séparateurs de milliers
    let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

    // Mettre à jour le champ2 avec la somme formatée
    document.getElementById(champ2).value = sommeFormatee;
}

function Somme() {
    var mont01 = document.getElementById('Autredep1').value;
    var mont02 = document.getElementById('Autredep2').value;
    var mont03 = document.getElementById('Autredep3').value;
    var montIndemTotal = document.getElementById('TotalidemForfait').value;
    var Smont01 = parseFloat(mont01.replace(/\./g, '').replace(',', ''));
    var Smont02 = parseFloat(mont02.replace(/\./g, '').replace(',', ''));
    var Smont03 = parseFloat(mont03.replace(/\./g, '').replace(',', ''));
    var SmontIndemTotal = parseFloat(montIndemTotal.replace(/\./g, '').replace(',', ''));
    if (mont01 === "") {
        Smont01 = 0
    }
    if (mont02 === "") {
        Smont02 = 0
    }
    if (mont03 === "") {
        Smont03 = 0
    }
    if (montIndemTotal === "") {
        SmontIndemTotal = 0
    }
    var options = {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    };
    var Somme = parseInt(Smont01, 10) + parseInt(Smont02) + parseInt(Smont03);
    var TotalAutre = document.getElementById('TotalAutredep')
    TotalAutre.value = Somme.toLocaleString('en-US', options).replace(/,/g, '.');

    STotalAutre = parseFloat(TotalAutre.value.replace(/\./g, '').replace(',', '.'));
    var SommeTo = parseInt(STotalAutre) + parseInt(SmontIndemTotal);
    var NetPaie = document.getElementById('Alldepense')
    NetPaie.value = SommeTo.toLocaleString('en-US', options).replace(/,/g, '.');

}

function calculerSomme(champA, champB, champC, TotalC) {
    // Récupérer les valeurs des deux champs
    let valeurChampA = parseFloat(document.getElementById(champA).value.replace('.', '')) || 0;
    let valeurChampB = parseFloat(document.getElementById(champB).value.replace('.', '')) || 0;
    let valeurChampC = parseFloat(document.getElementById(champC).value.replace('.', '')) || 0;
    // Calculer la somme
    let somme = valeurChampA + valeurChampB + valeurChampC;

    // Formater la somme avec des séparateurs de milliers
    let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

    // Mettre à jour le champ sommeTotal avec la somme formatée
    document.getElementById(TotalC).value = sommeFormatee;
}

function calculerSommeAll(champA, champB, champC, TotalAll) {
    // Récupérer les valeurs des deux champs
    let valeurChampA = parseFloat(document.getElementById(champA).value.replace('.', '')) || 0;
    let valeurChampB = parseFloat(document.getElementById(champB).value.replace('.', '')) || 0;
    let valeurchampC = parseFloat(document.getElementById(champC).value.replace('.', '')) || 0;
    // Calculer la somme
    let somme = (valeurChampA + valeurChampB) - valeurchampC;

    // Formater la somme avec des séparateurs de milliers
    let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

    // Mettre à jour le champ sommeTotal avec la somme formatée
    document.getElementById(TotalAll).value = sommeFormatee;
}



function Interne_externe() {
    var Interne = document.getElementById('Interne');
    var externe = document.getElementById('externe');
    var IntServ = document.getElementById('int');
    var ExtServ = document.getElementById('ext');
    var checkInterne = document.getElementById('radiochek').value;
    var OptInt = document.getElementById('OpInter');
    var OptExt = document.getElementById('OpExter');

    if (checkInterne === 'Interne') {
        externe.style.display = 'none';
        // Interne.style.display = 'block'
        // IntServ.style.display = 'block';
        ExtServ.style.display = 'none';
        // OptInt.style.display = 'block';
        OptExt.style.display = 'none';
    } else {
        // externe.style.display = 'block';
        Interne.style.display = 'none';
        IntServ.style.display = 'none';
        // ExtServ.style.display = 'block';
        OptInt.style.display = 'none';
        // OptExt.style.display = 'Block';
    }
}

function Difference_date() {
    var DD = document.getElementById('dateDebut').value;
    var DF = document.getElementById('dateFin').value;
    var DateD = new Date(DD);
    var DateF = new Date(DF);
    if (DateD > DateF) {
        alert('Merci de vérifier la date précédente ');
    }
}

function typeCatge() {
    var catgRental = document.getElementById('MUTARENTAL');
    var catgSTD = document.getElementById('categ');
    var TypeMiss = document.getElementById('typeMission').value;
    var check = document.getElementById('radiochek').value;
    var codeservint = document.getElementById('ServINt').value;
    var codeservExt = document.getElementById('Serv').value;
    if (check === 'Interne') {
        codeSer = codeservint;
    } else {
        codeSer = codeservExt;
    }
    if (codeSer === '50 Rental' && TypeMiss == 'MUTATION') {
        catgRental.style.display = 'block';
        catgSTD.style.display = 'none';
    } else {
        catgRental.style.display = 'none';
        catgSTD.style.display = 'bloxk';
    }
}

function negative(TotalAll) {
    let valeur_TotalAll = parseFloat(document.getElementById(TotalAll).value.replace('.', '')) || 0;
    if (valeur_TotalAll < 0) {
        document.getElementById(TotalAll).value = 0;
    }
}

function sommeEtIndemniteDeplac(champA, champB, champC) {
    // Récupérer les valeurs des deux champs
    let valeurChampA = parseFloat(document.getElementById(champA).value.replace(/[^\d]/g, '')) || 0;
    let valeurChampB = document.getElementById(champB).value;

    // Calculer la somme
    let somme = valeurChampA * valeurChampB;

    // Formater la somme avec des séparateurs de milliers
    let sommeFormatee = somme.toLocaleString('en-US').replace(/,/g, '.');

    // Mettre à jour le champ2 avec la somme formatée
    document.getElementById(champC).value = sommeFormatee;

}



window.addEventListener('load', function() {
    visible_espece();
    Interne_externe(); 
    typeCatge();
});