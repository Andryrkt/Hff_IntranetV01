const swalStyles = {
  success: {
    background: "#d1e7dd",
    color: "#0f5132",
    iconColor: "#198754",
    toast: true,
    position: "top-start",
    timer: 7000,
    timerProgressBar: true,
    showConfirmButton: false,
  },
  error: {
    background: "#f8d7da",
    color: "#842029",
    iconColor: "#dc3545",
    toast: false, // Important : erreur = plus visible
    showConfirmButton: true,
    confirmButtonColor: "#dc3545",
  },
};

export function showNotification() {
  const notification = document.getElementById("alert-notification");

  if (notification) {
    let type = notification.dataset.notificationType;
    const message = notification.dataset.notificationMessage;

    type = type === "success" ? "success" : "error";
    bootstrapNotify(
      type,
      type === "success" ? "Opération réussie" : "Echec de l'opération",
      message
    );
  }
}

export function bootstrapNotify(type, title, text = "") {
  Swal.fire({
    icon: type,
    title: `<strong>${title}</strong>`,
    html: text,
    ...swalStyles[type],
  });
}
