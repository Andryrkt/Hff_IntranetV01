// document.addEventListener("DOMContentLoaded", function () {
//   const toutLivrerBtn = document.querySelector("#tout-livre");
//   const partiellementLivreBtn = document.querySelector("#partiellement-livre");
//   const partiellementDispoBtn = document.querySelector("#partiellement-dispo");
//   const completNonLivreBtn = document.querySelector("#complet-non-livre");
//   const backOrderBtn = document.querySelector("#back-order");
//   const toutAfficherBtn = document.querySelector("#tout-afficher");

//   toutLivrerBtn.addEventListener("click", () => {

//   });
// });

document.addEventListener("DOMContentLoaded", function () {
  const buttons = {
    "tout-livre": "tout-livre",
    "partiellement-livre": "partiellement-livre",
    "partiellement-dispo": "partiellement-dispo",
    "complet-non-livre": "complet-non-livre",
    "back-order": "back-order",
    "tout-afficher": null, // Tout afficher n'a pas de classe spécifique
  };

  // Ajoute un gestionnaire d'événement pour chaque bouton
  for (const [buttonId, filterClass] of Object.entries(buttons)) {
    const button = document.getElementById(buttonId);
    if (button) {
      button.addEventListener("click", () => filterRowsByColumn(filterClass));
    }
  }

  function filterRowsByColumn(filterClass) {
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach((row) => {
      let hasMatchingCell = false;

      // Parcourt toutes les cellules de la ligne
      const cells = row.querySelectorAll("td");
      cells.forEach((cell) => {
        const links = cell.querySelectorAll("a"); // Tous les liens dans la cellule

        if (!filterClass) {
          // Si "Tout afficher", montre toutes les lignes et cellules
          links.forEach((link) => (link.style.display = ""));
          hasMatchingCell = true; // La ligne reste visible
        } else {
          // Filtre par classe
          let cellMatches = false;
          links.forEach((link) => {
            if (link.classList.contains(filterClass)) {
              link.style.display = ""; // Affiche les liens correspondant
              cellMatches = true;
            } else {
              link.style.display = "none"; // Cache les liens non correspondants
            }
          });

          if (cellMatches) {
            hasMatchingCell = true; // Marque la ligne comme ayant une correspondance
          }
        }
      });

      // Masque ou affiche la ligne entière
      row.style.display = hasMatchingCell ? "" : "none";
    });
  }
});

/**===============================
 * ACCORDION STYCKI
 *================================*/

function adjustStickyPositions() {
  const accordion = document.getElementById("formAccordion");
  const stickyStatut = document.querySelector(".sticky-header-statut");
  const tableHeader = document.querySelector(".table-plein-ecran thead tr");

  // Vérifiez la hauteur totale de l'accordéon ouvert
  const accordionHeight = accordion ? accordion.offsetHeight : 0;

  if (stickyStatut) {
    stickyStatut.style.top = `${accordionHeight + 53}px`;
  }

  if (tableHeader) {
    tableHeader.style.top = `${
      accordionHeight + stickyStatut.offsetHeight + 49
    }px`;
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
