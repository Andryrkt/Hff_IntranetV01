const notificationToast = new bootstrap.Toast('#notificationToast'); // création de toast de notification
const notificationIcon = document.getElementById('toast-notification-icon');
const notificationContent = document.getElementById(
  'toast-notification-content'
);

/**
 * Méthode pour afficher un message toast
 *
 * @param {string} type
 * @param {string} message le message à afficher dans le toast
 */
export function afficherToast(type, message) {
  notificationIcon.innerHTML = '';
  notificationContent.innerHTML = '';
  let icon = '';

  switch (type) {
    case 'annulation':
      icon = `<i class="fa-solid fa-circle-minus text-secondary"></i>`;
      break;

    case 'success':
      icon = `<i class="fa-solid fa-circle-check text-success"></i>`;
      break;

    case 'erreur':
      icon = `<i class="fa-solid fa-ban text-danger"></i>`;
      break;

    default:
      break;
  }

  notificationIcon.innerHTML = icon;
  notificationContent.innerHTML = message;

  notificationToast.show();
}
