export function showNotification() {
  const notification = document.getElementById("alert-notification");

  if (notification) {
    const type = notification.dataset.notificationType;
    const message = notification.dataset.notificationMessage;

    Swal.fire({
      icon: type,
      title: message,
    });
  }
}
