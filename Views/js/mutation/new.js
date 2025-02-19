import { updateDropdown } from '../utils/selectionHandler';

const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
const placeholder = ' -- Choisir un service -- ';
const spinnerElement = document.querySelector('#spinner-service-debiteur');
const containerElement = document.querySelector('#service-debiteur-container');

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
