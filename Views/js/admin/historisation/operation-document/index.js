import { fetchData } from '../../../utils/fetchUtils';
import { baseUrl } from '../../../utils/config';

const spinner = document.getElementById('spinner');

// Fonction pour afficher et masquer le spinner
function toggleSpinner(show) {
  spinner.style.display = show ? 'block' : 'none';
}

async function loadOperations() {
  toggleSpinner(true); // Afficher le spinner pendant la récupération des données
  const operations = await fetchData(
    `${baseUrl}api/operation-document-fetch-all`
  ); // Données JSON injectées par le back-end
  toggleSpinner(false); // Masquer le spinner après la récupération des données
  return operations;
}

// Initialisation des opérations
let operations = [];
loadOperations().then((data) => {
  operations = data;
  renderTable(operations);
});

function adjustStickyPositions() {
  const stickyStatut = document.querySelector('.sticky-header');
  const tableHeader = document.querySelector('.sticky-table-header');

  // Vérifiez la hauteur totale de l'accordéon ouvert
  const accordionHeight = stickyStatut.offsetHeight;

  console.log(accordionHeight);

  tableHeader.style.top = `${accordionHeight}px`;
}

window.addEventListener('resize', adjustStickyPositions);
