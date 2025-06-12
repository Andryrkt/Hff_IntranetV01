import Validator from 'validatorjs';
import { FetchManager } from './../FetchManager.js';

const fetchManager = new FetchManager();
export const form = document.form;
const agenceDestinataire = form.agenceDestinataire;
const serviceDestinataire = form.serviceDestinataire;

export function fetchData(selectOption = undefined) {
  fetchManager
    .get('serviceDestinataire')
    .then((data) => {
      console.log(data);

      //Sélectionner l'option spécifiée
      if (selectOption === undefined) {
        setTimeout(() => {
          //selectOption = document.getElementById('agenceDestinataire').value.toUpperCase();
          selectOption = agenceDestinataire.value.toUpperCase();
          //console.log(selectOption);
        }, 1000);
      }

      setTimeout(() => {
        //console.log(selectOption);
        //const serviceDestinataire = document.getElementById('serviceDestinataire');
        let taille = data[selectOption].length;
        //console.log(taille);
        let optionsHTML = '';
        for (let i = 0; i < taille; i++) {
          optionsHTML += `<option value="${data[selectOption][
            i
          ].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
        }
        serviceDestinataire.innerHTML = optionsHTML;
      }, 1000); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
    })
    .catch((error) => {
      console.error(error);
    });
}

export function changeService() {
  var selectedOption = this.value.toUpperCase();
  fetchData(selectedOption);
}

export const send = (event) => {
  event.preventDefault();

  let data = {
    motifArretMateriel: motifArretMateriel,
    nomClient: nomClient,
    prixHt: prixHt,
    motifMiseRebut: motifMiseRebut,
  };

  let rules = {
    motifArretMateriel: 'required|max:100',
    nomClient: 'max:50',
    motifMiseRebut: 'max:100',
  };

  let messages = {
    'required.motifArretMateriel': 'Le champ email est obligatoire.',
    'max.motifArretMateriel': 'caractères maximum: 100',
    'max.nomClient': 'caractères maximum: 50',
    'max.motifMiseRebut': 'caractères maximum: 100',
  };

  let validation = new Validator(data, rules, messages);

  if (validation.passes()) {
    console.log('Validation avec succes');
  } else {
    console.log('Validation failed');
    const errors = validation.errors.all();
    console.log(errors);

    for (let field in errors) {
      document.querySelector(`#error-${field}`).textContent = errors[field][0]; // Affiche le premier message d'erreur pour chaque champ
    }
  }
};
