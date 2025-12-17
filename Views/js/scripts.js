import { baseUrl } from "./utils/config";
import { FetchManager } from "./api/FetchManager";
import { initSessionTimer } from "./utils/session/sessionTimer";
import { displayOverlay } from "./utils/ui/overlay";
import { preloadAllData } from "./da/data/preloadData";
import { showNotification } from "./utils/notification/notification";

document.addEventListener("DOMContentLoaded", () => {
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
   * PRELOAD DATA POUR LA DA     *
   *=============================*/
  const hasDAPinput = document.getElementById("hasDAP");

  if (hasDAPinput) {
    console.log("hasDAPinput existe");
    console.log("hasDAPinput.dataset.hasDAP = " + hasDAPinput.dataset.hasDap);
    localStorage.setItem("hasDAP", hasDAPinput.dataset.hasDap);
  } else {
    console.log("hasDAPinput n'existe pas");
  }

  if (localStorage.getItem("hasDAP") === "1") {
    (async () => {
      await preloadAllData();
    })();
  } else {
    console.log("Pas besoin de preloadData");
  }

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
   * TOOLTIP BOOTSTRAP           *
   *=============================*/
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      new bootstrap.Tooltip(el);
    });
  });

  /*====================================*
   * MODAL TYPE DE DEMANDE PAIEMENT     *
   *====================================*/
  const fetchManager = new FetchManager();
  const modalTypeDemande = document.getElementById("modalTypeDemande");
  if (modalTypeDemande) {
    modalTypeDemande.addEventListener("click", (event) => {
      event.preventDefault();

      // 1. Prépare et affiche la modale avec un spinner
      const modalContent = document.getElementById("modalContent");
      const formModalEl = document.getElementById("formModal");
      const formModal = bootstrap.Modal.getOrCreateInstance(formModalEl);

      modalContent.innerHTML = `
        <div class="d-flex justify-content-center my-5">
            <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;
      formModal.show();

      // 2. Récupère le contenu
      const url = "api/form-type-demande";
      fetchManager
        .get(url, "text")
        .then((html) => {
          modalContent.innerHTML = html;
          // Attache le listener au formulaire fraîchement injecté
          const typeDemandeForm = document.getElementById("typeDemandeForm");
          if (typeDemandeForm) {
            typeDemandeForm.addEventListener("submit", function (submitEvent) {
              submitEvent.preventDefault();

              const formData = new FormData(this);
              let jsonData = {};
              formData.forEach((value, key) => {
                let cleanKey = key.replace(
                  /^form_type_demande\[(.*?)\]$/,
                  "$1"
                );
                jsonData[cleanKey] = value;
              });

              if (jsonData.typeDemande === "1") {
                window.location.href = `${baseUrl}/compta/demande-de-paiement/new/${jsonData.typeDemande}`;
              } else if (jsonData.typeDemande === "2") {
                window.location.href = `${baseUrl}/compta/demande-de-paiement/new/${jsonData.typeDemande}`;
              }
            });
          }
        })
        .catch((error) => {
          console.error("Erreur lors du chargement du formulaire:", error);
          modalContent.innerHTML = `
                <div class="alert alert-danger m-3">
                    Une erreur est survenue lors du chargement du contenu. Veuillez réessayer.
                </div>`;
        });
    });
  }

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
