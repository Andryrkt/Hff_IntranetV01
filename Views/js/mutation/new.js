import { updateDropdown } from '../utils/selectionHandler';
import { handleAvance } from './handleAvanceIndemnite';

document.addEventListener('DOMContentLoaded', function () {
  /** Agence et service */
  const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
  const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
  const placeholder = ' -- Choisir une service débiteur -- ';
  const spinnerElement = document.querySelector('#spinner-service-debiteur');
  const containerElement = document.querySelector(
    '#service-debiteur-container'
  );

  agenceDebiteurInput?.addEventListener('change', function () {
    if (agenceDebiteurInput.value !== '') {
      updateDropdown(
        serviceDebiteurInput,
        `/Hffintranet/agence-fetch/${agenceDebiteurInput.value}`,
        placeholder,
        spinnerElement,
        containerElement
      );
    }
  });

  /** Avance sur indemnité de chantier */
  const avanceSurIndemnite = document.getElementById(
    'mutation_form_avanceSurIndemnite'
  );

  avanceSurIndemnite.addEventListener('change', function () {
    handleAvance(this.value);
  });
});
