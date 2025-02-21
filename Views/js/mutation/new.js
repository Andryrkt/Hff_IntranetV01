import { updateDropdown } from '../utils/selectionHandler';

document.addEventListener('DOMContentLoaded', function () {
  const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
  const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
  const placeholder = ' -- Choisir une service débiteur -- ';
  const spinnerElement = document.querySelector('#spinner-service-debiteur');
  const containerElement = document.querySelector(
    '#service-debiteur-container'
  );

  agenceDebiteurInput?.addEventListener('change', function () {
    if (agenceDebiteurInput.value !== '') {
      const url = `/Hffintranet/agence-fetch/${agenceDebiteurInput.value}`;
      updateDropdown(
        serviceDebiteurInput,
        url,
        placeholder,
        spinnerElement,
        containerElement
      );
    }
  });
});
