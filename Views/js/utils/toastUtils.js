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
  let icon = '';
  notificationIcon.innerHTML = icon;
  notificationContent.innerHTML = message;
  notificationToast.show();
}
