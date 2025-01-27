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

  // Modification d'un ticket
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
        // Si l'utilisateur peut modifier le ticket, on redirige directement
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

  // Cloture d'un ticket
  const clotureModal = new bootstrap.Modal(
    document.getElementById('modalCloture')
  );

  const clotureModalButtons = document.querySelectorAll('.cloturer-ticket');

  clotureModalButtons.forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault(); // Empêche le comportement par défaut du lien
      const profil = event.target.getAttribute('data-tik-profil');
      const statut = event.target.getAttribute('data-tik-statut');
      const modalBodyContent = document.getElementById('modal-cloture-content');

      modalBodyContent.textContent = '';

      // Vérification et gestion de la logique
      const message = getClotureMessage(profil, statut);

      if (message) {
        // Si un message existe, on l'affiche dans la modale
        modalBodyContent.textContent = message;
        clotureModal.show();
      } else {
        // Si pas de message, on redirige immédiatement
        window.location.href = event.target.getAttribute('href');
      }
    });
  });

  /**
   * Fonction pour déterminer le message ou retourner null si aucune erreur
   * @param {string} profil - Profil de l'utilisateur
   * @param {string} statut - Statut du ticket
   * @returns {string|null} - Message d'erreur ou null si pas d'erreur
   */
  function getClotureMessage(profil, statut) {
    // Vérifications liées au profil
    if (profil === '-1')
      return `Vous ne pouvez clôturer que votre propre ticket.`;
    if (profil === '0')
      return `En tant qu'intervenant, vous n'avez pas l'autorisation pour clôturer un ticket.`;

    // Vérifications liées au statut
    if (statut === '64') return `Un ticket clôturé ne peut être reclôturé.`;
    if (statut === '59') return `Un ticket refusé ne peut être clôturé.`;

    // Cas spécifique : profil = 1 (client) et ticket pas résolu
    if (profil === '1' && statut !== '62') {
      return `Vous ne pouvez clôturer que des tickets résolus.`;
    }

    // Aucun problème détecté
    return null;
  }
});
