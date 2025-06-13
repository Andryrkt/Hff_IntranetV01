import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterUneLigne, autocompleteTheFields } from '../new/dal';
import { eventOnFamille } from '../new/event';
import { formatAllField, onFileNamesInputChange } from '../new/field';

document.addEventListener('DOMContentLoaded', function () {
  buildIndexFromLinesAndBindEvents();

  document.querySelectorAll('.trombone-add-pj').forEach((el) => {
    el.addEventListener('click', function () {
      this.closest('.DAL-container') // le plus proche conteneur de la ligne DA
        .querySelector('input[type="file"]') // trouver l'input file dans ce conteneur
        .click();
    });
  });

  document
    .querySelectorAll('[id^="demande_appro_form_DAL_"][id$="_fileNames"]')
    .forEach((inputFile) => {
      inputFile.accept = '.pdf, image/*'; // Accepter les fichiers PDF et images
      inputFile.addEventListener('change', (event) =>
        onFileNamesInputChange(event)
      );
    });

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

  document.querySelectorAll('.delete-DA').forEach((deleteButton) => {
    deleteButton.addEventListener('click', function () {
      deleteLigneDa(this);
    });
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

function deleteLigneDa(button) {
  if (
    confirm(
      'Êtes-vous sûr(e) de vouloir retirer cette ligne de demande d’achat ?'
    )
  ) {
    let prototypeId = button.getAttribute('prototype-id');
    let container = document.getElementById(
      `demande_appro_form_DAL_${prototypeId}`
    );
    let deletedCheck = document.getElementById(
      `demande_appro_form_DAL_${prototypeId}_deleted`
    );
    container.classList.add('d-none'); // cacher la ligne de DA
    deletedCheck.checked = true; // cocher le champ deleted
  }
}
