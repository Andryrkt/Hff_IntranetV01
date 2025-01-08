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
    handleActionClick('planifier');
  }

  // Boutons d'action
  const buttons = [
    { id: '#btn_resoudre', action: 'resoudre' },
    { id: '#btn_transferer', action: 'transferer' },
    { id: '#btn_planifier', action: 'planifier' },
  ];

  buttons.forEach(({ id, action }) => {
    const btn = document.querySelector(id);
    btn?.addEventListener('click', () => handleActionClick(action));
  });

  // champs Intervenant et Date de planning
  const tikIntervenant = document.querySelector('#detail_tik_intervenant');
  const dateDebutPlanning = document.querySelector(
    '#detail_tik_dateDebutPlanning'
  );
  const dateFinPlanning = document.querySelector('#detail_tik_dateFinPlanning');

  const transfererBtn = document.getElementById('#btn_transferer');

  // gestion du cas où l'intervenant n'est pas valide
  tikIntervenant.addEventListener('change', () =>
    validateField(
      true,
      tikIntervenant.value,
      (val) => val !== transfererBtn.getAttribute('data-intervenant'),
      document.querySelector('.error-message-intervenant')
    )
  );

  // gestion de cas où la date de planning est invalide
  [dateDebutPlanning, dateFinPlanning].forEach((date) => {
    date.addEventListener('change', () =>
      validateField(
        true,
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
    console.log(buttonName);

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
