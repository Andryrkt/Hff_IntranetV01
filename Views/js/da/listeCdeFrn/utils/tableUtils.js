export function updateRowState(checkbox, isChecked) {
  const cell = checkbox.closest("td");
  if (!cell) return;

  let currentCell = cell;
  while (currentCell) {
    currentCell.classList.toggle("td-active", isChecked);
    currentCell = currentCell.nextElementSibling;
  }
}

export function toggleCheckbox(checkbox, isChecked) {
  checkbox.checked = isChecked;
  updateRowState(checkbox, isChecked);
}

export function resetAllChecks(checkboxes) {
  checkboxes.forEach((cb) => toggleCheckbox(cb, false));
}
