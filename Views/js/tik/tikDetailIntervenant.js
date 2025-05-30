import { handleActionClick } from './tikFormHandler.js';

import {
  validateField,
  validateFieldDate,
  validateFormBeforeSubmit,
} from '../utils/formUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  handleActionClick('debut', 'formTik');

  // Boutons d'action
  const buttons = [
    { id: '#btn_resoudre', action: 'resoudre' },
    { id: '#btn_transferer', action: 'transferer' },
    { id: '#btn_planifier', action: 'planifier' },
  ];

  buttons.forEach(({ id, action }) => {
    const btn = document.querySelector(id);
    btn?.addEventListener('click', () => handleActionClick(action, 'formTik'));
  });

  // champs Intervenant et Date de planning
  const tikIntervenant = document.querySelector('#detail_tik_intervenant');
  const dateDebutPlanning = document.querySelector(
    '#detail_tik_dateDebutPlanning'
  );
  const dateFinPlanning = document.querySelector('#detail_tik_dateFinPlanning');

  const transfererBtn = document.getElementById('btn_transferer');

  // gestion du cas où l'intervenant n'est pas valide
  tikIntervenant?.addEventListener('change', () =>
    validateField(
      true,
      tikIntervenant.value,
      (val) => val !== transfererBtn.getAttribute('data-intervenant'),
      document.querySelector('.error-message-intervenant')
    )
  );

  // gestion de cas où la date de planning est invalide
  [dateDebutPlanning, dateFinPlanning].forEach((date) => {
    date?.addEventListener('change', () =>
      validateFieldDate(
        true,
        dateDebutPlanning.value,
        dateFinPlanning.value,
        document.querySelector('.error-message-date')
      )
    );
  });

  // Formulaire avant submit
  const myForm = document.getElementById('formTik');

  // Bloquer le formulaire si champ invalide
  myForm?.addEventListener('submit', (event) => {
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
        validateFieldDate(
          buttonName === 'planifier',
          dateDebutPlanning.value,
          dateFinPlanning.value,
          document.querySelector('.error-message-date')
        ),
    ]);
  });
});
