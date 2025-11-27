import { baseUrl } from "./utils/config";

import { FetchManager } from "./api/FetchManager";
import { initSessionTimer } from "./utils/session/sessionTimer";
import { displayOverlay } from "./utils/ui/overlay";
import { preloadAllData } from "./da/data/preloadData";

document.addEventListener("DOMContentLoaded", function () {
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

  // Lorsque l'utilisateur clique sur le lien de déconnexion
  logoutLink?.addEventListener("click", function (event) {
    event.preventDefault();
    logoutModal.show();
  });

  confirmLogout?.addEventListener("click", () => {
    window.location.href = logoutUrl;
  });

  /*=============================*
   * PRELOAD DATA POUR LA DA     *
   *=============================*/
  const hasDAPinput = document.getElementById("hasDAP"); // savoir si l'utilisateur a l'autorisation de l'application DAP

  if (hasDAPinput) {
    console.log("hasDAPinput existe");
    console.log("hasDAPinput.dataset.hasDAP = " + hasDAPinput.dataset.hasDap);
    localStorage.setItem("hasDAP", hasDAPinput.dataset.hasDap);
  } else {
    console.log("hasDAPinput n'existe pas");
  }

  if (localStorage.getItem("hasDAP") === "1") {
    (async () => {
      await preloadAllData(); // préchargement des données dans fournisseur et désignation
    })();
  } else {
    console.log("Pas besoin de preloadData");
  }

  /*=============================*
   * LES DROPDOWNS               *
   *=============================*/
  document
    .querySelectorAll(".dropdown-menu .dropdown-toggle")
    .forEach(function (element) {
      element.addEventListener("click", function (e) {
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
    modalTypeDemande.addEventListener("click", function (event) {
      event.preventDefault();
      const overlay = document.getElementById("loading-overlays");
      overlay.classList.remove("hidden");
      const url = "api/form-type-demande"; // L'URL de votre route Symfony
      fetchManager
        .get(url, "text")
        .then((html) => {
          document.getElementById("modalContent").innerHTML = html;
          new bootstrap.Modal(document.getElementById("formModal")).show();

          // Ajouter un écouteur sur la soumission du formulaire
          document
            .getElementById("typeDemandeForm")
            .addEventListener("submit", function (event) {
              event.preventDefault();

              const formData = new FormData(this);

              let jsonData = {};
              formData.forEach((value, key) => {
                // Supprimer le préfixe `form_type_demande[...]`
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
