// const loader = document.querySelector(".loader");

// window.addEventListener("load", () => {
//   loader.classList.add("fondu-out");
// });

let timeout;

// Variables pour le chronomètre
const totalTime = 900; // Total en secondes (15 minutes)
let timeRemaining = totalTime;

const chronoText = document.getElementById('chrono-text');
const chronoContainer = document.querySelector('.chrono-container');
const chronoProgress = document.querySelector('.chrono-progress');

if (location.pathname === '/Hffintranet/') {
  chronoContainer.classList.add('d-none');
}

//Calcul du périmètre du cercle (2 * PI * r)
const radius = 45;
const circumference = 2 * Math.PI * radius;

if (chronoProgress?.style) {
  chronoProgress.style.strokeDasharray = circumference;
}

// Fonction pour mettre à jour le chrono
function updateChrono() {
  timeRemaining--;

  // Calculer le pourcentage de progression
  const progressPercentage = timeRemaining / totalTime;
  const dashOffset = circumference * (1 - progressPercentage);
  if (chronoProgress?.style) {
    chronoProgress.style.strokeDashoffset = dashOffset;
  }

  // Mettre à jour le texte
  const hours = Math.floor(timeRemaining / 3600);
  const minutes = Math.floor((timeRemaining % 3600) / 60);
  const seconds = timeRemaining % 60;
  if (chronoText?.textContent) {
    chronoText.textContent = `${hours}:${minutes
      .toString()
      .padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  }
  // Rediriger à la fin
  if (timeRemaining <= 0) {
    clearInterval(timer);
    window.location.href = '/Hffintranet/logout';
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
  localStorage.setItem('session-active', Date.now());

  // Redémarrer le timer du chrono
  timer = setInterval(updateChrono, 1000);

  // Définir un nouveau timeout pour la déconnexion
  timeout = setTimeout(function () {
    window.location.href = '/Hffintranet/logout'; // URL de déconnexion
  }, 900000); // 15 minutes
}

// Définir les événements pour détecter l'activité utilisateur
const events = [
  'load',
  'mousemove',
  'keypress',
  'touchstart',
  'click',
  'scroll',
];
events.forEach((event) => window.addEventListener(event, resetTimeout));

// Surveiller les changements dans localStorage pour synchroniser les onglets
window.addEventListener('storage', function (event) {
  if (event.key === 'session-active') {
    resetTimeout();
  }
});

// Vérification régulière de l'expiration de la session
function checkSessionExpiration() {
  const lastActive = localStorage.getItem('session-active');
  const now = Date.now();

  if (lastActive && now - lastActive > 900000) {
    window.location.href = '/Hffintranet/logout'; // Rediriger vers la déconnexion
  }
}

// Vérifiez l'expiration à intervalles réguliers (toutes les 10 secondes)
setInterval(checkSessionExpiration, 10000);

// Démarrer le timeout et le chrono au chargement de la page
resetTimeout();
/**
 * modal pour la déconnexion
 */
document.addEventListener('DOMContentLoaded', function () {
  // Sélectionner le lien de déconnexion et le modal
  const logoutLink = document.getElementById('logoutLink');
  const logoutModal = new bootstrap.Modal(
    document.getElementById('logoutModal')
  );
  const confirmLogout = document.getElementById('confirmLogout');

  // Variable pour stocker l'URL de déconnexion (ou la logique)
  let logoutUrl = logoutLink.getAttribute('href');

  // Lorsque l'utilisateur clique sur le lien de déconnexion
  logoutLink.addEventListener('click', function (event) {
    // Empêcher la redirection initiale (si nécessaire)
    event.preventDefault();
    // Afficher le modal de confirmation
    logoutModal.show();
  });

  // Lorsque l'utilisateur clique sur le bouton "Confirmer"
  confirmLogout.addEventListener('click', function () {
    // Effectuer la déconnexion (rediriger vers l'URL de déconnexion)
    window.location.href = logoutUrl; // Effectuer la déconnexion
  });
});

/**
 * POUR LE TOOLTIP
 */
document.addEventListener('DOMContentLoaded', function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/** OVERLAY */
// Afficher l'overlay dès que la page commence à charger
document.addEventListener('DOMContentLoaded', () => {
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
});

// Masquer l'overlay après le chargement de la page
window.addEventListener('load', () => {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) {
    overlay.classList.add('hidden'); // Masquer l'overlay après le chargement
  }
});
