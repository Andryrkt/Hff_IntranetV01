export function clearTableContents(tableId) {
  const table = document.getElementById(tableId);
  if (table) {
    table.innerHTML = "";
  }
}

export function displayEmptyMessage(containerId) {
  const container = document.getElementById(containerId);
  if (container) {
    container.innerHTML =
      '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
  }
}
