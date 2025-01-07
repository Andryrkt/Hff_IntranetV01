import { resetDropdown } from '../utils/dropdownUtils.js';

import { updateDropdown } from '../utils/selectionHandlerUtils.js';

import { setupModal } from '../utils/modalHandlerUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  const elements = [
    {
      firstInput: '.agenceEmetteur',
      secondInput: '.serviceEmetteur',
      thirdInput: '',
      spinner: '#spinner-service-emetteur',
      container: '#service-emetteur-container',
      fetchUrl: (value) => `/Hffintranet/agence-fetch/${value}`,
      placeholder: ' -- Choisir un service -- ',
    },
    {
      firstInput: '.agenceDebiteur',
      secondInput: '.serviceDebiteur',
      thirdInput: '',
      spinner: '#spinner-service-debiteur',
      container: '#service-debiteur-container',
      fetchUrl: (value) => `/Hffintranet/agence-fetch/${value}`,
      placeholder: ' -- Choisir un service -- ',
    },
    {
      firstInput: '.categorie',
      secondInput: '.sous-categorie',
      thirdInput: '.autres-categories',
      spinner: '#spinner-sous-categorie',
      container: '#sous-categorie-container',
      fetchUrl: (value) => `/Hffintranet/api/sous-categorie-fetch/${value}`,
      placeholder: ' -- Choisir une sous-catégorie -- ',
    },
    {
      firstInput: '.sous-categorie',
      secondInput: '.autres-categories',
      thirdInput: '',
      spinner: '#spinner-autres-categories',
      container: '#autres-categories-container',
      fetchUrl: (value) => `/Hffintranet/api/autres-categorie-fetch/${value}`,
      placeholder: ' -- Choisir une autre catégorie -- ',
    },
  ];

  elements.forEach(
    ({
      firstInput,
      secondInput,
      thirdInput,
      spinner,
      container,
      fetchUrl,
      placeholder,
    }) => {
      const firstElement = document.querySelector(firstInput);
      const secondElement = document.querySelector(secondInput);
      const spinnerElement = document.querySelector(spinner);
      const containerElement = document.querySelector(container);

      firstElement?.addEventListener('change', function () {
        if (firstElement.value !== '') {
          const url = fetchUrl(firstElement.value);
          updateDropdown(
            secondElement,
            url,
            placeholder,
            spinnerElement,
            containerElement
          );
        }
        if (thirdInput !== '') {
          const thirdElement = document.querySelector(thirdInput);
          if (thirdElement.value !== '') {
            resetDropdown(thirdElement, ' -- Choisir une autre catégorie -- ');
          }
        }
      });
    }
  );

  setupModal('confirmationModal', 'modifierLink', 'confirmModification'); // modal pour la modification d'un ticket
});
