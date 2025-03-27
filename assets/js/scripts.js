import { baseUrl } from "./utils/config";
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

const loader = document.querySelector(".loader");

// window.addEventListener("load", () => {
//   loader.classList.add("fondu-out");
// });
let timeout;

// Variables pour le chronomètre
const totalTime = 900; // Total en secondes (15 minutes)
let timeRemaining = totalTime;

const chronoText = document.getElementById("chrono-text");
const chronoContainer = document.querySelector(".chrono-container");
const chronoProgress = document.querySelector(".chrono-progress");
const chronoText = document.getElementById("chrono-text");
const chronoContainer = document.querySelector(".chrono-container");
const chronoProgress = document.querySelector(".chrono-progress");

if (location.pathname === `${baseUrl}/` && chronoContainer != null) {
  chronoContainer.classList.add("d-none");
}

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
      chronoProgress.style.backgroundColor = "#4caf50"; // Vert
    } else if (progressPercentage > 20) {
      chronoProgress.style.backgroundColor = "#ff9800"; // Orange
      chronoProgress.style.backgroundColor = "#ff9800"; // Orange
    } else {
      chronoProgress.style.backgroundColor = "#f44336"; // Rouge
      chronoProgress.style.backgroundColor = "#f44336"; // Rouge
    }
  }

  // Mettre à jour le texte
  const hours = Math.floor(timeRemaining / 3600);
  const minutes = Math.floor((timeRemaining % 3600) / 60);
  const seconds = timeRemaining % 60;
  if (chronoText?.textContent) {
    chronoText.textContent = `${minutes.toString().padStart(2, "0")}:${seconds
    chronoText.textContent = `${minutes.toString().padStart(2, "0")}:${seconds
      .toString()
      .padStart(2, "0")}`;
      .padStart(2, "0")}`;
  }

  // Rediriger à la fin
  if (timeRemaining <= 0) {
    clearInterval(timer);
    window.location.href = `${baseUrl}/logout`;
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
window.addEventListener("storage", function (event) {
  if (event.key === "session-active") {
    resetTimeout();
  }
});

// Vérification régulière de l'expiration de la session
function checkSessionExpiration() {
  const lastActive = localStorage.getItem("session-active");
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
  // Vérifier que Bootstrap est bien chargé
  if (!window.bootstrap) {
    console.error("Bootstrap n'est pas chargé correctement !");
    return;
  }

  // Sélectionner le lien de déconnexion et le modal
  const logoutLink = document.getElementById("logoutLink");
  const logoutModalElement = document.getElementById("logoutModal");
  const confirmLogout = document.getElementById("confirmLogout");

  // Vérifier si les éléments existent avant d'ajouter des événements
  if (!logoutLink || !logoutModalElement || !confirmLogout) {
    console.warn("Certains éléments de la déconnexion sont manquants.");
    return;
  }

  const logoutModal = new bootstrap.Modal(logoutModalElement);

  // Stocker l'URL de déconnexion
  let logoutUrl = logoutLink.getAttribute("href");

  // Lorsque l'utilisateur clique sur le lien de déconnexion
  logoutLink.addEventListener("click", function (event) {
    event.preventDefault(); // Empêcher la redirection immédiate
    logoutModal.show(); // Afficher le modal
  });

  // Lorsque l'utilisateur clique sur le bouton "Confirmer"
  confirmLogout.addEventListener("click", function () {
    if (logoutUrl) {
      window.location.href = logoutUrl; // Rediriger vers la déconnexion
    } else {
      console.error("URL de déconnexion introuvable.");
    }
  });
});

/**
 * modal pour la déconnexion
 */
document.addEventListener("DOMContentLoaded", function () {
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/**
 * MODAL TYPE DE DEMANDE Paiement
 */

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("modalTypeDemande")
    .addEventListener("click", function (event) {
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
const allButtonAfficher = document.querySelectorAll(".ajout-overlay");

// allButtonAfficher.forEach((button) => {
//   button.addEventListener('click', () => {
//     const overlay = document.getElementById('loading-overlay');
//     if (overlay) {
//       overlay.classList.remove('hidden'); // Affiche l'overlay
//     }
//   });
// });

// Masquer l'overlay après le chargement de la page
window.addEventListener("load", () => {
  const overlay = document.getElementById("loading-overlay");
window.addEventListener("load", () => {
  const overlay = document.getElementById("loading-overlay");
  if (overlay) {
    overlay.classList.add("hidden"); // Masquer l'overlay après le chargement
    overlay.classList.add("hidden"); // Masquer l'overlay après le chargement
  }
});
