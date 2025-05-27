import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterUneLigne, autocompleteTheFields } from '../new/dal';
import { eventOnFamille } from '../new/event';
import { formatAllField } from '../new/field';

document.addEventListener('DOMContentLoaded', function () {
  buildIndexFromLinesAndBindEvents();

  document
    .getElementById('add-child')
    .addEventListener('click', ajouterUneLigne);

  document.getElementById('myForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!document.getElementById('children-container').hasChildNodes()) {
      alert('Vous devez au moins ajouter une ligne de DA!');
    } else {
      document.getElementById('child-prototype').remove();
      document.getElementById('myForm').submit();
    }
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});

function buildIndexFromLinesAndBindEvents() {
  let maxIndex = 0;

  document.querySelectorAll("[id^='demande_appro_form_DAL_']").forEach((el) => {
    let match = el.id.match(/demande_appro_form_DAL_(\d+)$/);
    if (match) {
      let index = parseInt(match[1]);

      updateIdAndName(el, index);

      if (!isNaN(index) && index > maxIndex) {
        maxIndex = index;
      }

      eventOnFamille(index); // gestion d'évènement sur la famille et sous-famille à la ligne index
      formatAllField(index); // formater les champs à la ligne index
      autocompleteTheFields(index); // autocomplète les champs
    }
  });
  localStorage.setItem('index', maxIndex);
}

function updateIdAndName(inputs, newIndex) {
  inputs
    .querySelectorAll("[id^='demande_appro_form_DAL_']")
    .forEach((input) => {
      const oldId = input.id;
      const idMatch = oldId.match(
        /^demande_appro_form_DAL_(\d+)_([a-zA-Z0-9]+)$/
      );
      if (idMatch) {
        const [, oldIndex, fieldName] = idMatch;

        // Nouveau ID
        const newId = `demande_appro_form_DAL_${newIndex}_${fieldName}`;
        input.id = newId;

        // Nouveau name
        const newName = `demande_appro_form[DAL][${newIndex}][${fieldName}]`;
        input.name = newName;
      }
    });
}
