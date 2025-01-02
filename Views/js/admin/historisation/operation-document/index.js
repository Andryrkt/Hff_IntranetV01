import { fetchData } from '../../../tik/utils/fetchUtils';

const operations = await fetchData(
  '/Hffintranet/api/operation-document-fetch-all'
); // Données JSON injectées par le back-end

function renderOperationsTable(data) {
  const tableBody = document.getElementById('operationTable');
  tableBody.innerHTML = '';

  data.forEach((op) => {
    const row = `
                <tr>
                    <td>${op.numeroDocument}</td>
                    <td>${op.date}</td>
                    <td>${op.username}</td>
                    <td>${op.operationType}</td>
                    <td>${op.documentType}</td>
                    <td>${op.statut}</td>
                    <td>${op.libelle}</td>
                </tr>
            `;
    tableBody.innerHTML += row;
  });
}

// Initial rendering
renderOperationsTable(operations);
