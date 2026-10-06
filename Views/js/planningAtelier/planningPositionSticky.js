/**===============================
 * ACCORDION STYCKI
 *================================*/

function adjustStickyPositions() {
    const stickyTitre = document.querySelector(".sticky-header-titre");
    const tableHeader = document.querySelectorAll(".table-plein-ecran thead tr");
  
    if (stickyTitre) {
      stickyTitre.style.top = `0px`;
    }
  
    if (tableHeader) {
      tableHeader[1].style.top = `${stickyTitre.offsetHeight+33}px`;
      tableHeader[0].style.top = `${stickyTitre.offsetHeight}px`;
    }
    
  }
  
  // Ajoutez un écouteur d'événements pour surveiller l'ouverture/fermeture de l'accordéon
  document
    .querySelectorAll("#formAccordion .accordion-button")
    .forEach((button) => {
      button.addEventListener("click", () => {
        setTimeout(adjustStickyPositions, 300); // Délai pour permettre l'animation de l'accordéon
      });
    });
  
  // Exécutez le script une fois au chargement de la page
  window.addEventListener("DOMContentLoaded", adjustStickyPositions);
  window.addEventListener("resize", adjustStickyPositions);