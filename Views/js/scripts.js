import { baseUrl } from "./utils/config";
import { FetchManager } from "./api/FetchManager";
import { initSessionTimer } from "./utils/session/sessionTimer";
import { displayOverlay } from "./utils/ui/overlay";
import { showNotification } from "./utils/notification/notification";

document.addEventListener("DOMContentLoaded", () => {
  /*=============================*
   * TOOLTIP BOOTSTRAP           *
   *=============================*/
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
    new bootstrap.Tooltip(el);
  });

  const logoutLink = document.getElementById("logoutLink");
  const logoutUrl = logoutLink?.getAttribute("href");

  /*=============================*
   * TIMER DE SESSION            *
   *=============================*/
  initSessionTimer({ duration: 900, logoutUrl: `${baseUrl}/logout` });

  /*=============================*
   * NOTIFICATION                *
   *=============================*/
  showNotification();

  /*=============================*
   * MODAL POUR LA DECONNEXION   *
   *=============================*/
  const logoutModal = new bootstrap.Modal(
    document.getElementById("logoutModal")
  );
  const confirmLogout = document.getElementById("confirmLogout");

  logoutLink?.addEventListener("click", (event) => {
    event.preventDefault();
    logoutModal.show();
  });

  confirmLogout?.addEventListener("click", () => {
    window.location.href = logoutUrl;
  });

  /*=============================*
   * LES DROPDOWNS               *
   *=============================*/
  document
    .querySelectorAll(".dropdown-menu .dropdown-toggle")
    .forEach((element) => {
      element.addEventListener("click", (e) => {
        e.stopPropagation();
        e.nextElementSibling.classList.toggle("show");
      });
    });

  /*=============================*
   * OVERLAY                     *
   *=============================*/
  const allButtonAfficher = document.querySelectorAll(".ajout-overlay");
  allButtonAfficher.forEach((button) => {
    button.addEventListener("click", () => {
      displayOverlay(true);
    });
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
