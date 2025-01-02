import { fetchData } from '../../../tik/utils/fetchUtils';

const consultations = await fetchData(
  '/Hffintranet/api/consultation-page-fetch-all'
); // Données JSON injectées par le back-end

const buttonFilter = document.getElementById('filter');

buttonFilter.addEventListener('click', () => {
  const userFilter = document.getElementById('filterUser').value.toLowerCase();
  const pageFilter = document.getElementById('filterPage').value.toLowerCase();
  const dateFilter = format(document.getElementById('filterDate').value);

  const filtered = consultations.filter((item) => {
    console.log(item.date);
    console.log(dateFilter);
    console.log(item.date.startsWith(dateFilter));
    return (
      (!userFilter || item.user.toLowerCase().includes(userFilter)) &&
      (!pageFilter || item.page.toLowerCase().includes(pageFilter)) &&
      (!dateFilter || item.date.startsWith(dateFilter))
    );
  });

  renderTable(filtered);
});

function renderTable(data) {
  const tableBody = document.getElementById('consultationTable');
  tableBody.innerHTML = '';

  if (data.length == 0) {
    console.log('if');
    console.log(data.length);
    tableBody.innerHTML += `<tr><td colspan="5" class="text-center">Aucun résultat</td></tr>`;
  } else {
    console.log('else');
    console.log(data.length);
    data.forEach((item) => {
      const row = `
                  <tr>
                      <td>${item.user}</td>
                      <td>${item.page}</td>
                      <td>${item.date}</td>
                      <td>${item.params}</td>
                      <td>${item.machine}</td>
                  </tr>
              `;
      tableBody.innerHTML += row;
    });
  }
}

function format(date) {
  const dateObj = new Date(date);

  // Obtenir les composants de la date
  const jour = String(dateObj.getDate()).padStart(2, '0'); // Jour avec 2 chiffres (ex. 02)
  const mois = String(dateObj.getMonth() + 1).padStart(2, '0'); // Mois avec 2 chiffres (01-12)
  const annee = dateObj.getFullYear(); // Année

  return `${jour}-${mois}-${annee}`;
}

// Initial rendering
renderTable(consultations);
