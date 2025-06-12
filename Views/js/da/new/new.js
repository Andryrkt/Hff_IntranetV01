import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterUneLigne } from './dal';

document.addEventListener('DOMContentLoaded', function () {
  localStorage.setItem('index', 0); // initialiser le nombre de ligne à 0

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
