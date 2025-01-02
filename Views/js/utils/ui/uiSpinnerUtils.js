/**
 * Active ou désactive le spinner dans une interface utilisateur.
 * @param {HTMLElement} spinner - L'élément spinner.
 * @param {HTMLElement} container - Le conteneur parent du spinner.
 * @param {boolean} isLoading - Indique si le spinner doit être affiché ou masqué.
 */
export function toggleSpinner(spinner, container, isLoading) {
  if (isLoading) {
    spinner.style.display = "block";
    container.style.opacity = "0.3";
  } else {
    spinner.style.display = "none";
    container.style.opacity = "1";
  }
}
