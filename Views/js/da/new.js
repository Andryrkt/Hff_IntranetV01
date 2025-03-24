import { displayOverlay } from '../utils/spinnerUtils';
import { ajouterUneLigne } from './new/dal';

document.addEventListener('DOMContentLoaded', function () {
  localStorage.setItem('index', 0);

  document
    .getElementById('add-child')
    .addEventListener('click', ajouterUneLigne);
  
  document.getElementById('myForm').addEventListener('submit', function (e) {
    if (!document.getElementById('children-container').hasChildNodes()) {
      e.preventDefault();
      alert('Vous devez au moins ajouter une ligne de DA!');
    }
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
