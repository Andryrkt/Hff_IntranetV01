// const loader = document.querySelector(".loader");

// window.addEventListener("load", () => {
//   loader.classList.add("fondu-out");
// });

let timeout;

// Variables pour le chronomètre
const totalTime = 3600; // Total en secondes (1 heure)
let timeRemaining = totalTime;

const chronoText = document.getElementById("chrono-text");
const chronoProgress = document.querySelector(".chrono-progress");

//Calcul du périmètre du cercle (2 * PI * r)
const radius = 45;
const circumference = 2 * Math.PI * radius;
chronoProgress.style.strokeDasharray = circumference;

// Fonction pour mettre à jour le chrono
function updateChrono() {
  timeRemaining--;

  //Calculer le pourcentage de progression
  const progressPercentage = timeRemaining / totalTime;
  const dashOffset = circumference * (1 - progressPercentage);
  chronoProgress.style.strokeDashoffset = dashOffset;

  //Mettre à jour le texte
  const hours = Math.floor(timeRemaining / 3600);
  const minutes = Math.floor((timeRemaining % 3600) / 60);
  const seconds = timeRemaining % 60;
  chronoText.textContent = `${hours}:${minutes
    .toString()
    .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;

  // Rediriger à la fin
  if (timeRemaining <= 0) {
    clearInterval(timer);
    window.location.href = "/Hffintranet/logout";
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

  // Redémarrer le timer du chrono
  timer = setInterval(updateChrono, 1000);

  // Définir un nouveau timeout pour la déconnexion
  timeout = setTimeout(function () {
    window.location.href = "/Hffintranet/logout"; // URL de déconnexion
  }, 3600000); // 1 heures
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

// Démarrer le timeout et le chrono au chargement de la page
resetTimeout();

/**
 * POUR LE TOOLTIP
 */
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
