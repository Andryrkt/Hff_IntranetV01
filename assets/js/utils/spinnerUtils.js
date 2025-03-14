export function toggleSpinner(spinnerElement, containerElement, show) {
  if (spinnerElement && containerElement) {
    spinnerElement.style.display = show ? 'block' : 'none';
    containerElement.style.display = show ? 'none' : 'block';
  }
}
