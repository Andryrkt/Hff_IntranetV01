/**
 * Active ou désactive le spinner dans une interface utilisateur.
 * @param {HTMLElement} spinner - L'élément spinner.
 * @param {HTMLElement} container - Le conteneur parent du spinner.
 * @param {boolean} isLoading - Indique si le spinner doit être affiché ou masqué.
 */
export function toggleSpinner(spinner, container, isLoading) {
  if (isLoading) {
    spinner.style.display = "block";
    container.style.opacity = "0";
  } else {
    spinner.style.display = "none";
    container.style.opacity = "1";
  }
}

export function affichageOverlay() {
  const overlay = document.createElement("div");
  overlay.style.position = "fixed";
  overlay.style.top = "0";
  overlay.style.left = "0";
  overlay.style.width = "100%";
  overlay.style.height = "100%";
  overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
  overlay.style.zIndex = "9999";
  overlay.style.display = "flex";
  overlay.style.alignItems = "center";
  overlay.style.justifyContent = "center";
  overlay.innerHTML = `
          <div class="spinner"></div>
        `;
  document.body.appendChild(overlay);
}

export function affichageSpinner() {
  const style = document.createElement("style");
  style.innerHTML = `
          .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid rgb(219, 188, 52);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
          }
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `;
  document.head.appendChild(style);
}
