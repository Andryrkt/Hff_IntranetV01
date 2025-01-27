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

  const confirmationModal = new bootstrap.Modal(
    document.getElementById('confirmationModal')
  );

  const confirmationModalButtons = document.querySelectorAll('.editer-ticket');

  confirmationModalButtons.forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault(); // Empêche le comportement par défaut du lien
      const monTicket = event.target.getAttribute('data-tik-monticket'); // si ticket m'appartient
      const ticketOuvert = event.target.getAttribute('data-tik-ouvert'); // si ticket ouvert
      const modalBodyContent = document.getElementById('modal-modif-content');

      modalBodyContent.textContent = '';

      if (monTicket === '1' && ticketOuvert === '1') {
        // Si l'utilisateur peut modifier le ticket, on empêche l'affichage de la modale
        window.location.href = event.target.getAttribute('href');
      } else {
        if (monTicket === '0') {
          modalBodyContent.textContent = `Vous n'avez pas l'autorisation pour modifier ce ticket.`;
        } else {
          modalBodyContent.textContent = `Impossible de modifier ce ticket car il a été déjà validé/refusé.`;
        }

        // Manuellement ouvrir la modale avec Bootstrap
        confirmationModal.show();
      }
    });
  });
});
