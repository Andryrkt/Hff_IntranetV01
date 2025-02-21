import { updateDropdown } from '../utils/selectionHandler';

const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
const placeholder = ' -- Choisir une service débiteur -- ';
const spinnerElement = document.querySelector('#spinner-service-debiteur');
const containerElement = document.querySelector('#service-debiteur-container');

export function handleService() {
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
}
