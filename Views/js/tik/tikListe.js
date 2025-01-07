import { resetDropdown } from '../utils/dropdownUtils.js';

import { updateDropdown } from '../utils/selectionHandlerUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  const agenceEmetteurInput = document.querySelector('.agenceEmetteur');
  const serviceEmetteurInput = document.querySelector('.serviceEmetteur');
  const serviceEmetteurSpinner = document.querySelector(
    '#spinner-service-emetteur'
  );
  const serviceEmetteurContainer = document.querySelector(
    '#service-emetteur-container'
  );
  const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
  const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
  const serviceDebiteurSpinner = document.querySelector(
    '#spinner-service-debiteur'
  );
  const serviceDebiteurContainer = document.querySelector(
    '#service-debiteur-container'
  );
  const categorieInput = document.querySelector('.categorie');
  const sousCategorieInput = document.querySelector('.sous-categorie');
  const sousCategorieSpinner = document.querySelector(
    '#spinner-sous-categorie'
  );
  const sousCategorieContainer = document.querySelector(
    '#sous-categorie-container'
  );
  const autreCategorieInput = document.querySelector('.autres-categories');
  const autreCategorieSpinner = document.querySelector(
    '#spinner-autres-categories'
  );
  const autreCategorieContainer = document.querySelector(
    '#autres-categories-container'
  );

  // Mise à jour des services emetteurs
  agenceEmetteurInput?.addEventListener('change', function () {
    if (agenceEmetteurInput.value !== '') {
      const url = `/Hffintranet/agence-fetch/${agenceEmetteurInput.value}`;
      updateDropdown(
        serviceEmetteurInput,
        url,
        ' -- Choisir une service -- ',
        serviceEmetteurSpinner,
        serviceEmetteurContainer
      );
    }
  });

  // Mise à jour des services debiteurs
  agenceDebiteurInput?.addEventListener('change', function () {
    if (agenceDebiteurInput.value !== '') {
      const url = `/Hffintranet/agence-fetch/${agenceDebiteurInput.value}`;
      updateDropdown(
        serviceDebiteurInput,
        url,
        ' -- Choisir une service -- ',
        serviceDebiteurSpinner,
        serviceDebiteurContainer
      );
    }
  });

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

  /**
   * modal pour la modification d'un ticket
   */
  const modifierLink = document.getElementById('modifierLink');
  const modal = new bootstrap.Modal(
    document.getElementById('confirmationModal')
  );
  const confirmModification = document.getElementById('confirmModification');

  // Variable pour stocker l'URL de la redirection
  let redirectUrl = '';

  // Ajouter un événement de clic sur le lien
  modifierLink.addEventListener('click', function (event) {
    // Empêcher la redirection initiale
    event.preventDefault();

    // Sauvegarder l'URL pour la redirection après confirmation
    redirectUrl = this.getAttribute('href');

    // Afficher le modal
    modal.show();
  });

  // Ajouter un événement de clic sur le bouton "Confirmer"
  confirmModification.addEventListener('click', function () {
    // Effectuer la redirection après confirmation
    window.location.href = redirectUrl;
  });
});
