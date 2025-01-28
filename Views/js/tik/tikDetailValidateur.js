import { handleActionClick } from './tikFormHandler.js';

import { resetDropdown } from '../utils/dropdownUtils.js';

import { updateDropdown } from '../utils/selectionHandler.js';

document.addEventListener('DOMContentLoaded', function () {
  handleActionClick('valider', 'formTik');

  // Boutons d'action
  const buttons = [
    { id: '#btn_valider', action: 'valider' },
    { id: '#btn_commenter', action: 'commenter' },
    { id: '#btn_refuser', action: 'refuser' },
  ];

  buttons.forEach(({ id, action }) => {
    const btn = document.querySelector(id);
    btn?.addEventListener('click', () => handleActionClick(action, 'formTik'));
  });

  // catégorie, sous-catégorie et autre catégorie
  const categorieInput = document.querySelector('.categorie');
  const sousCategorieInput = document.querySelector('.sous-categorie');
  const sousCategorieSpinner = document.querySelector(
    '#spinner-sous-categorie'
  );
  const sousCategorieContainer = document.querySelector(
    '#sous-categorie-container'
  );
  const autreCategorieInput = document.querySelector('.autre-categorie');
  const autreCategorieSpinner = document.querySelector(
    '#spinner-autre-categorie'
  );
  const autreCategorieContainer = document.querySelector(
    '#autre-categorie-container'
  );

  // Mise à jour des sous-catégories
  categorieInput?.addEventListener('change', function () {
    if (categorieInput.value !== '') {
      const url = `/Hffintranet/api/sous-categorie-fetch/${categorieInput.value}`;
      updateDropdown(
        sousCategorieInput,
        url,
        ' -- Choisir une sous-catégorie -- ',
        sousCategorieSpinner,
        sousCategorieContainer
      );
    }
    if (autreCategorieInput.value !== '') {
      resetDropdown(autreCategorieInput, ' -- Choisir une autre catégorie -- ');
    }
  });

  // Mise à jour des autres catégories
  sousCategorieInput?.addEventListener('change', function () {
    if (sousCategorieInput.value !== '') {
      const url = `/Hffintranet/api/autres-categorie-fetch/${sousCategorieInput.value}`;
      updateDropdown(
        autreCategorieInput,
        url,
        ' -- Choisir une autre catégorie -- ',
        autreCategorieSpinner,
        autreCategorieContainer
      );
    }
  });
});
