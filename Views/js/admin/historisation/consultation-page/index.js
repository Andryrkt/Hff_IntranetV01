document.addEventListener('DOMContentLoaded', function () {
  adjustStickyPositions();

  // Ajoutez un écouteur d'événements pour surveiller l'ouverture/fermeture de l'accordéon
  document
    .querySelector('button.accordion-button.enteteAccordion')
    .addEventListener('click', function () {
      // Utilisation de délai pour laisser le temps à l'animation de se terminer
      setTimeout(adjustStickyPositions, 300); // durée de l'animation: estimé à 300ms
    });
});

function adjustStickyPositions() {
  const stickyStatut = document.querySelector('.sticky-header');
  const tableHeader = document.querySelector('.sticky-table-header');

  // Vérifiez la hauteur totale de l'accordéon ouvert
  const accordionHeight = stickyStatut.offsetHeight;

  console.log(accordionHeight);

  tableHeader.style.top = `${accordionHeight}px`;
}

// Formatage de la date
function format(date) {
  const dateObj = new Date(date);
  const jour = String(dateObj.getDate()).padStart(2, '0');
  const mois = String(dateObj.getMonth() + 1).padStart(2, '0');
  const annee = dateObj.getFullYear();

  return `${jour}-${mois}-${annee}`;
}
