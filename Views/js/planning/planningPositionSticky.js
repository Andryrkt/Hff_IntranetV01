/**===============================
 * ACCORDION STYCKI
 *================================*/

function adjustStickyPositions() {
    const stickyTitre = document.querySelector(".sticky-header-titre");
    const tableHeader = document.querySelector(".table-plein-ecran thead tr");
  
    if (stickyTitre) {
      stickyTitre.style.top = `0px`;
    }
  
    if (tableHeader) {
      tableHeader.style.top = `${
        stickyTitre.offsetHeight - 3
      }px`;
    }
    console.log(tableHeader.style.top);
    
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