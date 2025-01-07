import { handleActionClick } from './tikFormHandler.js';

import {
  validateField,
  validateFormBeforeSubmit,
  disableForm,
} from '../utils/formUtils.js';

import { resetDropdown } from '../utils/dropdownUtils.js';

import { updateDropdown } from '../utils/selectionHandlerUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('formTik').getAttribute('edit') === 'false') {
    disableForm('formTik');
  } else {
    handleActionClick('valider');
  }

  // Boutons d'action
  const buttons = [
    { id: '#btn_valider', action: 'valider' },
    { id: '#btn_commenter', action: 'commenter' },
    { id: '#btn_refuser', action: 'refuser' },
    { id: '#btn_resoudre', action: 'resoudre' },
    { id: '#btn_transferer', action: 'transferer' },
    { id: '#btn_planifier', action: 'planifier' },
  ];

  buttons.forEach(({ id, action }) => {
    const btn = document.querySelector(id);
    btn?.addEventListener('click', () => handleActionClick(action));
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

  // champs Intervenant et Date de planning
  const tikIntervenant = document.querySelector('#detail_tik_intervenant');
  const dateDebutPlanning = document.querySelector(
    '#detail_tik_dateDebutPlanning'
  );
  const dateFinPlanning = document.querySelector('#detail_tik_dateFinPlanning');

  // gestion du cas où l'intervenant n'est pas valide
  tikIntervenant.addEventListener('change', () =>
    validateField(
      tikIntervenant.value,
      (val) => val !== transfererBtn.getAttribute('data-intervenant'),
      document.querySelector('.error-message-intervenant')
    )
  );

  // gestion de cas où la date de planning est invalide
  [dateDebutPlanning, dateFinPlanning].forEach((date) => {
    date.addEventListener('change', () =>
      validateField(
        dateDebutPlanning.value,
        (val) => new Date(val) <= new Date(dateFinPlanning.value),
        document.querySelector('.error-message-date')
      )
    );
  });

  // Formulaire avant submit
  const myForm = document.getElementById('formTik');

  // Bloquer le formulaire si champ invalide
  myForm.addEventListener('submit', (event) => {
    let buttonName = event.submitter.name;
    validateFormBeforeSubmit(event, [
      () =>
        validateField(
          buttonName === 'transferer',
          tikIntervenant.value,
          (val) => val !== transfererBtn.getAttribute('data-intervenant'),
          document.querySelector('.error-message-intervenant')
        ),
      () =>
        validateField(
          buttonName === 'planifier',
          dateDebutPlanning.value,
          (val) => new Date(val) <= new Date(dateFinPlanning.value),
          document.querySelector('.error-message-date')
        ),
    ]);
  });
});
