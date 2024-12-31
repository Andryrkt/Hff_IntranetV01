const operations = []; // Données JSON injectées par le back-end

function renderOperationsTable(data) {
  const tableBody = document.getElementById('operationTable');
  tableBody.innerHTML = '';

  data.forEach((op) => {
    const row = `
                <tr>
                    <td>${op.documentNumber}</td>
                    <td>${op.date}</td>
                    <td>${op.user}</td>
                    <td>${op.operationType}</td>
                    <td>${op.documentType}</td>
                    <td>${op.status}</td>
                </tr>
            `;
    tableBody.innerHTML += row;
  });
}

// Initial rendering
renderOperationsTable(operations);
