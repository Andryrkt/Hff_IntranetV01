// Lancer au chargement et au resize
window.addEventListener("DOMContentLoaded", function () {
  adjustStickyTableHeaders();

  // Recalculer dès qu'un bloc au-dessus du tableau change de hauteur
  // (accordéon, cartes de statut, polices ou notifications chargées après coup)
  const observer = new ResizeObserver(adjustStickyTableHeaders);
  ["#main-nav-bar", "#fil-d-ariane", ".sticky-header-titre"].forEach((selector) => {
    const el = document.querySelector(selector);
    if (el) observer.observe(el);
  });
});
window.addEventListener("resize", adjustStickyTableHeaders);

/* ---------------- Helpers ---------------- */
function getElementHeight(el) {
  return el ? el.getBoundingClientRect().height : 0; // retourne la hauteur exacte avec décimales
}

/* ---------- Fonction principale ---------- */
function adjustStickyTableHeaders() {
  const navBar = document.getElementById("main-nav-bar");
  const breadcrumb = document.getElementById("fil-d-ariane");
  const stickyTitle = document.querySelector(".sticky-header-titre");
  const table = document.querySelector(".table-sticky");
  const headerRows = table ? table.querySelectorAll("thead tr") : [];

  if (!table || headerRows.length === 0) return;

  // L'en-tête doit se figer juste sous le bloc titre, là où celui-ci se fige (son "top" CSS).
  // Additionner nav + fil d'Ariane surestime cette position et pousse l'en-tête sur la 1re ligne.
  const baseOffset = stickyTitle
    ? (parseFloat(getComputedStyle(stickyTitle).top) || 0) + getElementHeight(stickyTitle)
    : (navBar ? getElementHeight(navBar) : 0) +
      (breadcrumb ? getElementHeight(breadcrumb) : 0);

  // Positionne chaque ligne d'en-tête de manière cumulative
  let decalage = stickyTitle
    ? parseInt(stickyTitle.dataset.decalage) || -6
    : -6;

  let currentOffset = baseOffset + decalage; // éviter les décalages
  headerRows.forEach((row) => {
    row.style.top = `${currentOffset}px`;
    currentOffset += getElementHeight(row);
  });

  // Ajouter une marge au tableau pour libérer la place du header sticky
  let tableMt = stickyTitle
    ? parseFloat(stickyTitle.dataset.tableMt) || 50
    : 50;
  table.style.marginTop = `${tableMt}px`;
}
