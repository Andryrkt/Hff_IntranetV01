const typeMouvemnetInput = document.querySelector('#typeMouvement');
const idMaterielInput = document.querySelector('#idMateriel');

const dateDemandeDebutInput = document.querySelector('#dateDemandeDebut');
const dateDemandeFinInput = document.querySelector('#dateDemandeFin');

const exportExcelButton = document.querySelector('#export');
const rechercheInput = document.querySelector('#recherche');
const resetInput = document.querySelector("#reset");
const nombreLigneInput = document.querySelector("#nombreLigne");
const nombreResultatInput = document.querySelector('#nombreResultat');

const urlStatut = "/Hffintranet/index.php?action=listStatut"

    /**
     * @Andryrkt
     * récupère les donnée JSON et faire le traitement du recherhce, affichage, export excel
     */
     async function fetchvaleur(){
        const url = "/Hffintranet/index.php?action=listJson";
        return  await fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
    }


fetchvaleur().then(raw_data => {


    console.log(raw_data);
    // afficher les donnée du selecte statut
    //SelectStatutValue1(raw_data);

    //SousTypeDoc(raw_data)
   
    dateDemandeDebutInput.addEventListener('change', (e) => {
        e.preventDefault();
        // Vérifier si la date de début est supérieure à la date de fin
        if (new Date(dateDemandeDebutInput.value) > new Date(dateDemandeFinInput.value)) {
     
            const message = document.getElementById('dateCreationMessage');
            message.textContent = "La date de début doit être inférieure à la date de fin.";
       
        } else {
            const message = document.getElementById('dateCreationMessage');
            message.textContent = '';
        }
    });


    dateDemandeFinInput.addEventListener('change', (e) => {
        e.preventDefault();
    
        if (new Date(dateDemandeDebutInput.value) > new Date(dateDemandeFinInput.value)) {
        
            const message = document.getElementById('dateCreationMessage');
            message.textContent = "La date de début doit être inférieure à la date de fin.";
       
        } else {
            const message = document.getElementById('dateCreationMessage');
            message.textContent = '';
        
        }

    });

   


    /* 
        bouton de recherche
    */
    recherche.addEventListener('click', (e) => {
        e.preventDefault();
        executeFiltreEtRendu()
    });


   /*
        bouton effacer tous les recherches
   */
    reset.addEventListener('click', (e) => {
        e.preventDefault();
        statutInput.value = "";
        matriculeInput.value = "";
        sousTypeDocInput.value = "";
        dateCreationDebutInput.value = "";
        dateCreationFinInput.value = "";
        dateDebutDebutInput.value = "";
        dateDebutFinInput.value = "";
    })

    /**
     *fonction qui rendre 
     *
     */
    function executeFiltreEtRendu() {
        let donner = filtre(raw_data);
        // Filtre les données
        console.log(donner);
        if (donner.length > 0) {
            const container = document.querySelector('#noResult');
            container.innerHTML = '';
            renderData1(donner);
            nombreResultat.textContent = donner.length + ' résultats';
        } else {
            //console.log(new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value))

            // Afficher un message si le tableau est vide
            const noResult = document.querySelector('#noResult');
            noResult.innerHTML = `<p class="fw-bold" style="text-align: center;">Il n'y a pas de données correspondant à votre recherche.</p>`;
            var container = document.getElementById('table-container');
            container.innerHTML = '';
            nombreResultat.textContent = 0 + ' résultats';

        }
    }

    //export excel
    exportExcelButton.addEventListener('click', () => {
        ExportExcel(raw_data);
    });

})
.catch(error => {
    console.error('There has been a problem with your fetch operation:', error);
});



/** 
* @Andryrkt
* remplire le selecte du statu 
*/
// function SelectStatutValue1(data) {
// const uniqueStatuts = new Set();
// data.forEach(element => uniqueStatuts.add(element.Statut));

// const select = document.getElementById('statut');

// // Ajouter une option vide
// const emptyOption = document.createElement('option');
// emptyOption.value = ""; // Valeur vide
// emptyOption.textContent = "Sélectionnez un statut"; // Texte optionnel
// select.appendChild(emptyOption);

// uniqueStatuts.forEach(statut => {
//     const option = document.createElement('option');
//     option.value = statut;
//     option.textContent = statut;
//     // Ajouter l'option à l'élément <select>
//     select.appendChild(option);
// });
// }

// function SousTypeDoc(data) {
//     const uniqueSousTypeDoc = new Set();
// data.forEach(element => uniqueSousTypeDoc.add(element.Sous_type_document));

// const select = document.getElementById('sousTypeDoc');

// // Ajouter une option vide
// const emptyOption = document.createElement('option');
// emptyOption.value = ""; // Valeur vide
// emptyOption.textContent = "Sélectionnez un sous type"; // Texte optionnel
// select.appendChild(emptyOption);

// uniqueSousTypeDoc.forEach(sousTypeDoc => {
//     const option = document.createElement('option');
//     option.value = sousTypeDoc;
//     option.textContent = sousTypeDoc;
//     // Ajouter l'option à l'élément <select>
//     select.appendChild(option);
// });

//}

/** 
* @Andryrkt
* rendre le tableau afficher sur l'écran
*/
function renderData1(data) {
    const trCorps = document.querySelector('#trCorps');

    // Création du corps du tableau s'il n'existe pas encore

    var container = document.getElementById('table-container');
    container.innerHTML = '';

    // Ajouter les nouvelles lignes pour les données filtrées
    data.forEach(function(item, index) {
        var row = document.createElement('tr');
        row.classList.add(index % 2 === 0 ? 'table-gray-700' : 'table-secondary'); // Alternance des couleurs de ligne
        // Ajouter un bouton de duplication à chaque ligne
    var duplicateButton = document.createElement('button');
    duplicateButton.innerHTML = `<a href="#" style="text-decoration: none; color: #000000; font-weight: 600">Dupliquer</a>`;
        //duplicateButton.innerHTML = `<a href="/Hffintranet/index.php?action=DuplifierForm&NumDOM=${item['Numero_Ordre_Mission']}&IdDOM=${item['ID_Demande_Ordre_Mission']}&check=${item['Matricule']}" style="text-decoration: none;
    //color: #000000; font-weight: 600">Dupliquer</a>`;
    duplicateButton.classList.add('btn', 'btn-warning', 'mx-2', 'my-2');
    duplicateButton.style.backgroundColor = '#FBBB01';
    

    row.appendChild(duplicateButton);

        for (var key in item) {

        
            var cellule = document.createElement('td');
            cellule.classList.add('w-50');


            // if (key === 'Matricule' || key === 'Date_Demande' || key === 'Date_Debut' || key === 'Date_Fin' || key === 'Nombre_Jour') {
            //     cellule.style.textAlign = 'center';
            // }

            // if (key === 'Total_Autres_Depenses' || key === 'Total_General_Payer') {
            //     cellule.style.textAlign = 'end';
            // }

            // if(key==='ID_Demande_Ordre_Mission'){
            //     cellule.style.display = 'none';
            // }

            if (key === 'Date_Demande' || key === 'Date_Mise_Location') {
                cellule.textContent = item[key].split('-').reverse().join('/');
             }else if (key === 'Numero_Ordre_Mission') {
                var lien = document.createElement('a');
                lien.href = `/Hffintranet/index.php?action=DetailDOM&NumDom=${item[key]}&Id=${item['ID_Demande_Ordre_Mission']}`; 
                lien.textContent = item[key];
                cellule.appendChild(lien);
            }else{
                cellule.textContent = item[key]
            }
            
            // Vérifier si la clé est "statut" et attribuer une classe en conséquence
            if (key === 'Statut') {
                switch (item[key]) {
                    case 'Ouvert':
                        cellule.style.backgroundColor = "#FBBB01";
                        break;
                    case 'Clôturé':
                        cellule.style.backgroundColor = "#34c924";
                        break;
                    case 'Annulé':
                        cellule.style.backgroundColor = "#FF0000";
                        break;
                    case 'Encours':
                        cellule.style.backgroundColor = "#77b5fe";
                    break;
                }
            }


        row.appendChild(cellule);
    }

   
    container.appendChild(row);
});
}



/**
* @Andryrkt
* filtrer les données JSON à partir des critère entrer par l'utilisateur
* returner une tableau de donnée filtré
* 
*/
function filtre(data) {
// Récupérer les valeurs des champs de saisie

//const critereStatutValue = statutInput.value.trim();
const critereTypeMouvemnetValue = typeMouvemnetInput.value.trim();
const critereIdMaterielValue = idMaterielInput.value;
const dateDemandeDebutValue = dateDemandeDebutInput.value;
const dateDemandeFinValue = dateDemandeFinInput.value;
   
  
// Filtrer les données en fonction des critères
return resultatsFiltres = data.filter(function(demande) {

    // Filtrer par statut (si un critère est fourni)
    var filtreTypeMouvemnet = !critereTypeMouvemnetValue || demande.Code_Mouvement === critereTypeMouvemnetValue;
    var filtreIdMateriel = !critereIdMaterielValue || demande.ID_Materiel;
    


    // Filtrer par date de début de création (si un critère est fourni)
    var filtreDateDebutDemande = !dateDemandeDebutValue || demande.Date_Demande >= dateDemandeDebutValue;
    // Filtrer par date de fin de création (si un critère est fourni)
    var filtreDateFinDemande = !dateDemandeFinValue || demande.Date_Demande <= dateDemandeFinValue;
    // Filtrer par date de création/demande (si un critère est fourni)
    var filtreDateDemande = !dateDemandeDebutValue || !dateDemandeFinValue || (demande.Date_Demande >= dateDemandeDebutValue && demande.Date_Demande <= dateDemandeFinValue);



   

    // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
    return (filtreTypeMouvemnet && filtreIdMateriel  && filtreDateDebutDemande && filtreDateFinDemande && filtreDateDemande) || (!critereTypeMouvemnetValue && !critereIdMaterielValue && !dateDemandeDebutValue && !dateDemandeFinValue  &&!filtreTypeMouvemnet && !filtreIdMateriel  && !filtreDateDebutDemande && !filtreDateFinDemande);
});
 

}

/**
* @Andryrkt
* cette fonction permet d'exporter les données filtrée ou non dans une fichier excel
*/
function ExportExcel(data) {

    // Filtre les données
    let donner = filtre(data);

    // Crée une feuille Excel
    const worksheet = XLSX.utils.json_to_sheet(donner);
    const workbook = XLSX.utils.book_new();

    // Ajoute les en-têtes à la feuille Excel
    const headers = [
        'Statut',
        'type de Mouvement',
        'Numéro de demande BADM',
        'Date de Demande',
        'Agence_Service_Emetteur', 
        'Casier_Emetteur',
        'Agence_Service_Destinataire' ,
        'Casier_Destinataire', 
        'Motif_Arret_Materiel', 
        'Etat_Achat', 
        'Date_Mise_Location', 
        'Cout_Acquisition', 
        'Amortissement', 
        'Valeur_Net_Comptable', 
        'Nom_Client'
];
XLSX.utils.sheet_add_aoa(worksheet, [headers], {
    origin: "A1"
});

// Ajoute la feuille Excel au classeur
XLSX.utils.book_append_sheet(workbook, worksheet, "Données");

// Télécharge le fichier Excel
XLSX.writeFile(workbook, "Exportation-Excel.xlsx", {
    compression: true
});


}