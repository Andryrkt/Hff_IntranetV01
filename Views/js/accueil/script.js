document.addEventListener("DOMContentLoaded", function () {
  // Ne plus afficher si l'utilisateur l'a déjà masqué
  const STORAGE_KEY = "welcomeAlertDismissed";
  if (localStorage.getItem(STORAGE_KEY) === "1") return;

  const userNameElement = document.getElementById("userName");
  const firstname = userNameElement.dataset.userFirstName; // prénom de l'utilisateur
  const lastname = userNameElement.dataset.userLastName; // nom de l'utilisateur

  Swal.fire({
    title: `Bienvenue <strong>${firstname} ${lastname}</strong> 👋 !`,
    html: `
      <p style="margin:0 0 8px;">
        Ravi de vous revoir&nbsp;!
      </p>
      <p style="margin:0 0 8px;">
        Pour bien démarrer, <strong>pensez à consulter le Guide utilisateur intranet</strong> en cliquant sur </strong> <br>
        <a class="guide-link d-inline me-1">
            <i class="fas fa-info pe-2"></i>
            Guide utilisateur intranet
        </a> situé <strong>en haut de la page .
      </p>
      <p class="text-danger">
          <strong><u>NB</u> :</strong> Veuillez appuyer sur 
          <kbd>Ctrl</kbd> + <kbd>F5</kbd> avant de commencer à naviguer.
      </p>
      <label style="display:flex; align-items:center; gap:.5rem; margin-top:.75rem; cursor:pointer;">
        <input id="dontShowAgain" type="checkbox">
        <span>Ne plus afficher ce message</span>
      </label>
    `,
    icon: "success",
    confirmButtonText: "OK, j’ai compris",
    showDenyButton: false,
    allowOutsideClick: false,
    allowEscapeKey: true,
    focusConfirm: true,
    backdrop: true,
    customClass: {
      htmlContainer: "swal-text-left",
    },
    heightAuto: false,
  }).then(() => {
    // Gérer la case "ne plus afficher"
    const dontShow = document.getElementById("dontShowAgain")?.checked;
    if (dontShow) localStorage.setItem(STORAGE_KEY, "1");
  });
});
