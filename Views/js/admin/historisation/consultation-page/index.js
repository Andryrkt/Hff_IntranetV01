const consultations = []; // Données JSON injectées par le back-end

function applyFilters() {
  const userFilter = document.getElementById('filterUser').value.toLowerCase();
  const pageFilter = document.getElementById('filterPage').value.toLowerCase();
  const dateFilter = document.getElementById('filterDate').value;

  const filtered = consultations.filter((item) => {
    return (
      (!userFilter || item.user.toLowerCase().includes(userFilter)) &&
      (!pageFilter || item.page.toLowerCase().includes(pageFilter)) &&
      (!dateFilter || item.date.startsWith(dateFilter))
    );
  });

  renderTable(filtered);
}

function renderTable(data) {
  const tableBody = document.getElementById('consultationTable');
  tableBody.innerHTML = '';

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

// Initial rendering
renderTable(consultations);
