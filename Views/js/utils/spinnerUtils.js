export function toggleSpinner(spinnerElement, containerElement, show) {
  if (spinnerElement && containerElement) {
    spinnerElement.style.display = show ? 'block' : 'none';
    containerElement.style.display = show ? 'none' : 'block';
  }
}

export function displayOverlay(bool) {
  const overlay = document.getElementById('loading-overlay');
  if (bool) {
    overlay.style.display = 'flex';
  } else {
    overlay.style.display = 'none';
  }
}

export function toggleSpinners(spinnerService, serviceContainer, show) {
  spinnerService.style.display = show ? "inline-block" : "none";
  serviceContainer.style.display = show ? "none" : "block";
}
