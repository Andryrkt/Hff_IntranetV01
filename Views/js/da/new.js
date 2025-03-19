import { ajouterUneLigne } from './new/dal';

document.addEventListener('DOMContentLoaded', function () {
  localStorage.setItem('index', 0);

  document
    .getElementById('add-child')
    .addEventListener('click', ajouterUneLigne);
  document.querySelectorAll('[id*="artFams1"]').forEach((famille) => {
    famille.addEventListener('click', function () {});
  });
});
