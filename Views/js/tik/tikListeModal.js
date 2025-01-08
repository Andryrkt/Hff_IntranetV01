import { setupModal } from '../utils/modalHandlerUtils.js';
import { getFrenchMonth } from '../utils/dateUtils.js';
import { fetchData } from '../utils/fetchUtils.js';
import { toggleSpinner } from '../utils/spinnerUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  /** COMMENTAIRE MODAL */
  const commentaireModal = document.getElementById('commentaire');

  commentaireModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const text = button.getAttribute('data-original-text');

    const modalBodyContent = document.getElementById(
      'modal-commentaire-content'
    );

    if (text === '--') {
      modalBodyContent.textContent = 'Pas de commentaire';
    } else {
      const user = button.getAttribute('data-commentaire-user');
      const day = button.getAttribute('data-commentaire-day');
      const month = button.getAttribute('data-commentaire-month');
      const year = button.getAttribute('data-commentaire-year');
      const time = button.getAttribute('data-commentaire-time');
      modalBodyContent.innerHTML = `
        <p><strong>Auteur:</strong> ${user}</p>
        <p><strong>Date et heure:</strong> ${day} ${getFrenchMonth(
        month
      )} ${year} à ${time}</p>
        <p><strong>Commentaire:</strong> ${text}</p>`;
    }
  });

  const confirmationModal = document.getElementById('confirmationModal');

  confirmationModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const condition = button.getAttribute('data-bool'); // boolean
    const numTik = button.getAttribute('data-id'); // numéro du ticket
    const modalBodyContent = document.getElementById('modal-modif-content');
    const modalConfirmationSpinner = document.querySelector(
      '#spinner-confirmation-modal'
    );
    const modalConfirmationContainer = document.querySelector(
      '#confirmation-modal-container'
    );

    if (condition === '1') {
      // Si l'utilisateur peut modifier le ticket, on empêche l'affichage de la modale
      event.preventDefault();
      window.location.href = button.getAttribute('href');
    }

    try {
      // Affiche le spinner avant de lancer le fetch
      toggleSpinner(modalConfirmationSpinner, modalConfirmationContainer, true);
      const data = fetchData(
        `/Hffintranet/api/modification-ticket-fetch/${numTik}`
      );

      modalBodyContent.textContent = data.edit;
      if (!data.edit) {
        modalBodyContent.textContent = `Vous n'avez pas l'autorisation pour modifier le ticket \"${numTik}\".`;
      } else if (data.ouvert) {
        modalBodyContent.textContent = `Impossible de modifier le ticket \"${numTik}\" car il a été déjà validé.`;
      }
    } catch (error) {
      console.error('Erreur lors de la mise à jour des données:', error);
    } finally {
      // Désactive le spinner une fois le traitement terminé
      toggleSpinner(
        modalConfirmationSpinner,
        modalConfirmationContainer,
        false
      );
    }
  });
});
