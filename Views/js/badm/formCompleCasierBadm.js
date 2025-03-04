import { FetchManager } from './../FetchManager.js';

const fetchManager = new FetchManager();

console.log(fetchManager);
export function fetchCasier(selectOption = undefined) {
  //const fetchManager = new FetchManager('/Hffintranet/');
  fetchManager
    .get('casierDestinataire')
    .then((data) => {
      console.log(data);
      //Sélectionner l'option spécifiée
      if (selectOption === undefined) {
        setTimeout(() => {
          selectOption = document
            .getElementById('agenceDestinataire')
            .value.toUpperCase();
          console.log(selectOption);
        }, 1000);
      }

      setTimeout(() => {
        //console.log(selectOption);
        //console.log('okey');
        const casierDestinataire =
          document.getElementById('casierDestinataire');
        let taille = data[selectOption].length;
        //console.log(taille);
        let optionsHTML = ''; // Chaîne pour stocker les options HTML
        for (let i = 0; i < taille; i++) {
          optionsHTML += `<option value="${data[selectOption][
            i
          ].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
        }
        casierDestinataire.innerHTML = optionsHTML;
      }, 1000);
    })
    .catch((error) => console.error(error));
}

export function changeCasier() {
  var selectedOption = this.value.toUpperCase();
  fetchCasier(selectedOption);
}
