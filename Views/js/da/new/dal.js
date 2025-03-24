import { autocompleteTheFields } from './autocompletion';
import { eventOnFamille } from './event';
import {
  createDesiAndAppendTo,
  createFams2AndAppendTo,
  createFieldAndAppendTo,
  createRemoveButtonAndAppendTo,
  formatAllField,
} from './field';

let container = document.getElementById('children-container');

export function ajouterUneLigne() {
  let newIndex = parseInt(localStorage.getItem('index')) + 1; // Déterminer un index unique pour les nouveaux champs
  localStorage.setItem('index', newIndex); // Changer la valeur de newIndex
  let prototype = document
    .getElementById('child-prototype')
    .firstElementChild.cloneNode(true); // Clonage du prototype

  // Mettre à jour dynamiquement les IDs et Names
  prototype.id = replaceNameToNewIndex(prototype.id, newIndex);
  prototype.querySelectorAll('[id], [name]').forEach(function (element) {
    element.id = element.id
      ? replaceNameToNewIndex(element.id, newIndex)
      : element.id;
    element.name = element.name
      ? replaceNameToNewIndex(element.name, newIndex)
      : element.name;
  });

  // Créer la structure Bootstrap "row g-3"
  let row = document.createElement('div');
  row.classList.add('row', 'g-3');

  let fields = [
    ['w-15', 'fams1'],
    ['w-20', 'fams2'],
    ['w-25', 'artDesi'],
    ['w-10', 'dateFinSouhaite'],
    ['w-5', 'qteDem'],
    ['w-23', 'commentaire'],
    ['d-none', 'artConstp'],
    ['d-none', 'artRefp'],
    ['d-none', 'artFams1'],
    ['d-none', 'artFams2'],
    ['d-none', 'numeroFournisseur'],
    ['d-none', 'nomFournisseur'],
  ];

  fields.forEach(function ([classe, fieldName]) {
    if (fieldName === 'fams2') {
      createFams2AndAppendTo(classe, prototype, row);
    } else if (fieldName === 'artDesi') {
      createDesiAndAppendTo(classe, prototype, row);
    } else {
      createFieldAndAppendTo(classe, prototype, fieldName, row);
    }
  });
  createRemoveButtonAndAppendTo(prototype, row);

  let div = document.createElement('div');
  div.classList.add('mt-3');

  // Ajouter la row complète dans le container
  prototype.appendChild(row);
  prototype.appendChild(div);
  container.appendChild(prototype);

  eventOnFamille();
  formatAllField();
  autocompleteTheFields();
}

function replaceNameToNewIndex(element, newIndex) {
  return element.replace('__name__', newIndex);
}
