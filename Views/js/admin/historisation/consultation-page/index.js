import { fetchData } from '../../../tik/utils/fetchUtils';

const consultations = await fetchData(
  '/Hffintranet/api/consultation-page-fetch-all'
); // Données JSON injectées par le back-end
const buttonFilter = document.getElementById('filter');

buttonFilter.addEventListener('click', () => {
  const userFilter = getFilterValue('filterUser');
  const pageFilter = getFilterValue('filterPage');
  const dateFilter = format(getFilterValue('filterDate'));

  const filteredConsultations = filterConsultations(
    consultations,
    userFilter,
    pageFilter,
    dateFilter
  );
  renderTable(filteredConsultations);
});

// Extraction des valeurs de filtre
function getFilterValue(filterId) {
  return document.getElementById(filterId).value.toLowerCase();
}

// Filtrage des consultations
function filterConsultations(data, userFilter, pageFilter, dateFilter) {
  return data.filter((item) => {
    return (
      (!userFilter || item.user.toLowerCase().includes(userFilter)) &&
      (!pageFilter || item.page.toLowerCase().includes(pageFilter)) &&
      (!dateFilter || item.date.startsWith(dateFilter))
    );
  });
}

// Rendu de la table
function renderTable(data) {
  const tableBody = document.getElementById('consultationTable');
  tableBody.innerHTML =
    data.length === 0
      ? '<tr><td colspan="5" class="text-center">Aucun résultat</td></tr>'
      : data.map((item) => createTableRow(item)).join('');
}

// Création d'une ligne de tableau
function createTableRow(item) {
  return `
    <tr>
      <td>${item.user}</td>
      <td>${item.page}</td>
      <td>${item.date}</td>
      <td>${item.params}</td>
      <td>${item.machine}</td>
    </tr>
  `;
}

// Formatage de la date
function format(date) {
  const dateObj = new Date(date);
  const jour = String(dateObj.getDate()).padStart(2, '0');
  const mois = String(dateObj.getMonth() + 1).padStart(2, '0');
  const annee = dateObj.getFullYear();

  return `${jour}-${mois}-${annee}`;
}

// Rendu initial
renderTable(consultations);
