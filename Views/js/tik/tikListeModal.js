import { setupModal } from '../utils/modalHandlerUtils.js';
import { getFrenchMonth } from '../utils/dateUtils.js';

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
    const id = button.getAttribute('data-id'); // Extract info from data-* attributes

    const modalBodyContent = document.getElementById('modal-modif-content');

    if (text === '--') {
      modalBodyContent.textContent = 'Pas de commentaire';
    } else {
      modalBodyContent.textContent = text;
    }
  });
});

setupModal('confirmationModal', 'modifierLink', 'confirmModification'); // modal pour la modification d'un ticket
