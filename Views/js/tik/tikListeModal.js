import { getFrenchMonth } from '../utils/dateUtils.js';
import { updateMessage } from '../utils/messageHandler.js';

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

  const confirmationModal = new bootstrap.Modal(
    document.getElementById('confirmationModal')
  );

  const confirmationModalButtons = document.querySelectorAll(
    'a[data-bs-target="#confirmationModal"]'
  );

  confirmationModalButtons.forEach((element) => {
    element.addEventListener('click', (event) => {
      const condition = event.target.getAttribute('data-bool'); // boolean
      const numTik = event.target.getAttribute('data-id'); // numéro du ticket
      const modalBodyContent = document.getElementById('modal-modif-content');
      const modalConfirmationSpinner = document.querySelector(
        '#spinner-confirmation-modal'
      );
      const modalConfirmationContainer = document.querySelector(
        '#confirmation-modal-container'
      );

      modalBodyContent.textContent = '';

      console.log(condition);
      console.log(modalBodyContent.textContent);

      if (condition === '1') {
        // Si l'utilisateur peut modifier le ticket, on empêche l'affichage de la modale
        event.preventDefault();
        window.location.href = button.getAttribute('href');
      } else {
        updateMessage(
          confirmationModal,
          `/Hffintranet/api/modification-ticket-fetch/${numTik}`,
          modalBodyContent,
          modalConfirmationSpinner,
          modalConfirmationContainer
        );
      }
    });
  });
});
