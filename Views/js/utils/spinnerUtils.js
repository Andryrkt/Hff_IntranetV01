export function toggleSpinner(spinnerElement, containerElement, show) {
  if (spinnerElement && containerElement) {
    spinnerElement.style.display = show ? "block" : "none";
    containerElement.style.display = show ? "none" : "block";
  }
}

export function toggleSpinners(spinnerService, serviceContainer, show) {
  spinnerService.style.display = show ? "inline-block" : "none";
  serviceContainer.style.display = show ? "none" : "block";
}
