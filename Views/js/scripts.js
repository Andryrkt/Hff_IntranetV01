import { baseUrl } from "./utils/config";

import { FetchManager } from "./api/FetchManager";
import { initSessionTimer } from "./utils/session/sessionTimer";
import { displayOverlay } from "./utils/ui/overlay";
import { preloadAllData } from "./da/data/preloadData";

document.addEventListener("DOMContentLoaded", () => {
  const logoutLink = document.getElementById("logoutLink");
  const logoutUrl = logoutLink?.getAttribute("href");

  /*=============================*
   * TIMER DE SESSION            *
   *=============================*/
  initSessionTimer({ duration: 900, logoutUrl: logoutUrl });

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
      const overlay = document.getElementById("loading-overlays");
      overlay.classList.remove("hidden");
      const url = "api/form-type-demande";
      fetchManager
        .get(url, "text")
        .then((html) => {
          document.getElementById("modalContent").innerHTML = html;
          new bootstrap.Modal(document.getElementById("formModal")).show();

          document
            .getElementById("typeDemandeForm")
            .addEventListener("submit", (event) => {
              event.preventDefault();

              const formData = new FormData(this);

              let jsonData = {};
              formData.forEach((value, key) => {
                let cleanKey = key.replace(
                  /^form_type_demande\[(.*?)\]$/,
                  "$1"
                );
                jsonData[cleanKey] = value;

                console.log(jsonData.typeDemande === "1");
              });

              if (jsonData.typeDemande === "1") {
                window.location.href = `${baseUrl}/demande-paiement/${jsonData.typeDemande}`;
              } else if (jsonData.typeDemande === "2") {
                window.location.href = `${baseUrl}/demande-paiement/${jsonData.typeDemande}`;
              }
            });
        })
        .catch((error) =>
          console.error("Erreur lors du chargement du formulaire:", error)
        )
        .finally(() => {
          overlay.classList.add("hidden");
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
