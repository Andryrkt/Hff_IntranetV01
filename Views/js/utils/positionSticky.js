// Lancer au chargement et au resize
window.addEventListener("DOMContentLoaded", function () {
  adjustStickyTableHeaders();

  // Surveiller l'ouverture/fermeture de l'accordéon
  document
    .querySelectorAll("#formAccordion .accordion-button")
    .forEach((button) => {
      button.addEventListener("click", () => {
        setTimeout(adjustStickyTableHeaders, 300); // délai pour laisser l'animation se terminer
      });
    });
});
window.addEventListener("resize", adjustStickyTableHeaders);

function getElementHeight(el) {
  return el ? el.getBoundingClientRect().height : 0; // retourne la hauteur exacte avec décimales
}

function adjustStickyTableHeaders() {
  const navBar = document.getElementById("main-nav-bar");
  const breadcrumb = document.getElementById("fil-d-ariane");
  const stickyTitle = document.querySelector(".sticky-header-titre");
  const headerRows = document.querySelectorAll(".table-sticky thead tr");

  if (headerRows.length === 0) return;

  // Calcule la hauteur cumulée des éléments fixes au-dessus du tableau
  const baseOffset =
    (navBar ? getElementHeight(navBar) : 0) +
    (breadcrumb ? getElementHeight(breadcrumb) : 0) +
    (stickyTitle ? getElementHeight(stickyTitle) : 0);

  // Positionne chaque ligne d'en-tête de manière cumulative
  let currentOffset = baseOffset - 6; // éviter les décalages
  headerRows.forEach((row) => {
    row.style.top = `${currentOffset}px`;
    currentOffset += getElementHeight(row);
  });
}
