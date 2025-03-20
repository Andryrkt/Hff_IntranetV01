import { displayOverlay } from '../utils/spinnerUtils';
import { ajouterUneLigne } from './new/dal';

document.addEventListener('DOMContentLoaded', function () {
  localStorage.setItem('index', 0);

  document
    .getElementById('add-child')
    .addEventListener('click', ajouterUneLigne);
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
