import { FetchManager } from "./../FetchManager.js";

const fetchManager = new FetchManager('/Hffintranet/');
const form = document.form;

const agenceDestinataire = form.agenceDestinataire;
const serviceDestinataire = form.serviceDestinataire;


export function fetchData(selectOption = undefined) {
    //const fetchManager = new FetchManager('/Hffintranet/');
    fetchManager.get('index.php?action=detailJson')
    .then(data => {
            console.log(data);

            //Sélectionner l'option spécifiée
            if (selectOption === undefined) {
                setTimeout(() => {
                    //selectOption = document.getElementById('agenceDestinataire').value.toUpperCase();
                    selectOption = agenceDestinataire.value.toUpperCase();
                    //console.log(selectOption);
                }, 300);
            }


            setTimeout(() => {
                //console.log(selectOption);
                //const serviceDestinataire = document.getElementById('serviceDestinataire');
                let taille = data[selectOption].length;
                //console.log(taille);
                let optionsHTML = '';
                for (let i = 0; i < taille; i++) {
                    optionsHTML += `<option value="${data[selectOption][i].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
                }
                serviceDestinataire.innerHTML = optionsHTML;
            }, 300); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
        })
        .catch(error => {
            console.error(error);
        });
}