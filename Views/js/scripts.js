// const loader = document.querySelector(".loader");

// window.addEventListener("load", () => {
//   loader.classList.add("fondu-out");
// });

let timeout;

function resetTimeout() {
  clearTimeout(timeout);
  // Déconnecter l'utilisateur après 5 minutes (300000 millisecondes) d'inactivité
  timeout = setTimeout(function () {
    window.location.href = "/Hffintranet/logout"; // Définir votre URL de déconnexion
  }, 3600000);
}

// Réinitialiser le compteur à chaque interaction utilisateur
window.onload = resetTimeout;
window.onmousemove = resetTimeout;
window.onkeypress = resetTimeout;
window.ontouchstart = resetTimeout;

document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
