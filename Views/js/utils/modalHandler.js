// modalHandler.js
export function setupModal(modalId, triggerLinkId, confirmButtonId) {
  const modal = new bootstrap.Modal(document.getElementById(modalId));
  const triggerLink = document.getElementById(triggerLinkId);
  const confirmButton = document.getElementById(confirmButtonId);

  let redirectUrl = '';

  // Ajouter les écouteurs d'événements
  triggerLink?.addEventListener('click', (event) => {
    event.preventDefault();
    redirectUrl = event.target.getAttribute('href');
    modal.show();
  });

  confirmButton?.addEventListener('click', () => {
    if (redirectUrl) {
      window.location.href = redirectUrl;
    }
  });
}
