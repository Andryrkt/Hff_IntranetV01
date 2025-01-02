import { fetchData } from '../../../tik/utils/fetchUtils';

const spinner = document.getElementById('spinner');

// Fonction pour afficher et masquer le spinner
function toggleSpinner(show) {
  spinner.style.display = show ? 'block' : 'none';
}

async function loadOperations() {
  toggleSpinner(true); // Afficher le spinner pendant la récupération des données
  const operations = await fetchData(
    '/Hffintranet/api/operation-document-fetch-all'
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

function renderTable(data) {
  const tableBody = document.getElementById('operationTable');
  tableBody.innerHTML =
    data.length === 0
      ? '<tr><td colspan="7" class="text-center">Aucun résultat</td></tr>'
      : data.map((item) => createTableRow(item)).join('');
}

// Création d'une ligne de tableau
function createTableRow(item) {
  return `
    <tr>
      <td>${item.numeroDocument}</td>
      <td>${item.date}</td>
      <td>${item.username}</td>
      <td>${item.operationType}</td>
      <td>${item.documentType}</td>
      <td>${item.statut}</td>
      <td>${item.libelle}</td>
    </tr>
  `;
}
