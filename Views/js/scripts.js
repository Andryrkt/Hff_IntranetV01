import { baseUrl } from "./utils/config";

import { FetchManager } from "./api/FetchManager";
import { afficherToast } from "./utils/toastUtils";
import { displayOverlay } from "./utils/ui/overlay";
import { preloadAllData } from "./da/data/preloadData";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

let timeout;

// Variables pour le chronomètre
const totalTime = 900; // Total en secondes (15 minutes)
let timeRemaining = totalTime;

const chronoText = document.getElementById("chrono-text");
const chronoProgress = document.querySelector(".chrono-progress");

// Fonction pour mettre à jour le chrono
function updateChrono() {
  timeRemaining--;

  // Calculer le pourcentage de progression
  const progressPercentage = (timeRemaining / totalTime) * 100; // Pourcentage
  if (chronoProgress?.style) {
    chronoProgress.style.width = `${progressPercentage}%`;

    // Logique des couleurs
    if (progressPercentage > 50) {
      chronoProgress.style.backgroundColor = "#4caf50"; // Vert
    } else if (progressPercentage > 20) {
      chronoProgress.style.backgroundColor = "#ff9800"; // Orange
    } else {
      chronoProgress.style.backgroundColor = "#f44336"; // Rouge
    }
  }

  // Mettre à jour le texte
  const minutes = Math.floor((timeRemaining % 3600) / 60);
  const seconds = timeRemaining % 60;
  if (chronoText?.textContent) {
    chronoText.textContent = `${minutes.toString().padStart(2, "0")}:${seconds
      .toString()
      .padStart(2, "0")}`;
  }

  // Rediriger à la fin
  if (timeRemaining <= 0) {
    clearInterval(timer);
    window.location.href = `${baseUrl}/logout`;
  } else if (timeRemaining <= 15) {
    afficherToast("erreur", `Votre session va expiré dans ${timeRemaining} s.`);
  }
}

// Lancer le chrono
let timer = setInterval(updateChrono, 1000);

// Fonction pour réinitialiser le timeout et le chrono
function resetTimeout() {
  clearTimeout(timeout);
  clearInterval(timer);

  // Réinitialiser le chrono
  timeRemaining = totalTime;
  updateChrono(); // Mise à jour immédiate de l'affichage du chrono

  // Mettre à jour l'état dans localStorage
  localStorage.setItem("session-active", Date.now());

  // Redémarrer le timer du chrono
  timer = setInterval(updateChrono, 1000);

  // Définir un nouveau timeout pour la déconnexion
  timeout = setTimeout(function () {
    window.location.href = `${baseUrl}/logout`; // URL de déconnexion
  }, 900000); // 15 minutes
}

// Définir les événements pour détecter l'activité utilisateur
const events = [
  "load",
  "mousemove",
  "keypress",
  "touchstart",
  "click",
  "scroll",
];
events.forEach((event) => window.addEventListener(event, resetTimeout));

// Surveiller les changements dans localStorage pour synchroniser les onglets
window.addEventListener("storage", function (event) {
  if (event.key === "session-active") {
    resetTimeout();
  }
});

// Vérification régulière de l'expiration de la session
function checkSessionExpiration() {
  const lastActive = localStorage.getItem("session-active");
  const now = Date.now();

  if (lastActive && now - lastActive > 900000) {
    window.location.href = `${baseUrl}/logout`; // Rediriger vers la déconnexion
  }
}

// Vérifiez l'expiration à intervalles réguliers (toutes les 10 secondes)
setInterval(checkSessionExpiration, 10000);

// Démarrer le timeout et le chrono au chargement de la page
resetTimeout();

/**
 * modal pour la déconnexion
 */
document.addEventListener("DOMContentLoaded", function () {
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

  // Les dropdowns
  document
    .querySelectorAll(".dropdown-menu .dropdown-toggle")
    .forEach(function (element) {
      element.addEventListener("click", function (e) {
        e.stopPropagation();
        e.nextElementSibling.classList.toggle("show");
      });
    });

  // Sélectionner le lien de déconnexion et le modal
  const logoutLink = document.getElementById("logoutLink");
  const logoutModal = new bootstrap.Modal(
    document.getElementById("logoutModal")
  );
  const confirmLogout = document.getElementById("confirmLogout");

  // Variable pour stocker l'URL de déconnexion (ou la logique)
  let logoutUrl = logoutLink?.getAttribute("href");

  // Lorsque l'utilisateur clique sur le lien de déconnexion
  logoutLink?.addEventListener("click", function (event) {
    // Empêcher la redirection initiale (si nécessaire)
    event.preventDefault();
    // Afficher le modal de confirmation
    logoutModal.show();
  });

  // Lorsque l'utilisateur clique sur le bouton "Confirmer"
  confirmLogout?.addEventListener("click", function () {
    // Effectuer la déconnexion (rediriger vers l'URL de déconnexion)
    window.location.href = logoutUrl; // Effectuer la déconnexion
  });
});

/**
 * modal pour la déconnexion
 */
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/** ====================================
 * MODAL TYPE DE DEMANDE Paiement
 *=======================================*/

document.addEventListener("DOMContentLoaded", function () {
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
});

/** OVERLAY */
// Afficher l'overlay dès que la page commence à charger
/* document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) {
    overlay.classList.remove('hidden'); // S'assurer que l'overlay est visible au début
  }
});

window.addEventListener('beforeunload', function () {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) {
    overlay.classList.remove('hidden'); // Affiche l'overlay juste avant la redirection
  }
}); */

// Afficher l'overlay
const allButtonAfficher = document.querySelectorAll(".ajout-overlay");

allButtonAfficher.forEach((button) => {
  button.addEventListener("click", () => {
    displayOverlay(true);
  });
});

// Masquer l'overlay après le chargement de la page
window.addEventListener("load", () => {
  const overlay = document.getElementById("loading-overlay");
  if (overlay) {
    overlay.classList.add("hidden"); // Masquer l'overlay après le chargement
  }
});
