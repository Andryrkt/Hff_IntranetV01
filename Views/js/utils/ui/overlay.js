export function displayOverlay(afficher, message = "") {
  const ids = ["loading-overlays", "loading-overlay"];
  ids.forEach((id) => {
    const overlay = document.getElementById(id);
    if (overlay) {
      const textOverlay = overlay.querySelector(".text-overlay");
      if (afficher) {
        overlay.classList.add("active");
        overlay.style.display = "flex";
      } else {
        overlay.classList.remove("active");
        overlay.style.display = "none";
      }
      if (textOverlay) {
        textOverlay.textContent = message || "Veuillez patienter s'il vous plaît!";
      }
    }
  });
}

/**
 * Récupère la valeur d'un cookie par son nom.
 * @param {string} name Nom du cookie.
 * @returns {string|null} Valeur du cookie ou null s'il n'existe pas.
 */
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}

/**
 * Supprime un cookie.
 * @param {string} name Nom du cookie.
 */
function deleteCookie(name) {
  document.cookie = `${name}=; Max-Age=-99999999; path=/`;
}

/**
 * Surveille la présence d'un cookie pour masquer l'overlay après un téléchargement.
 */
export function monitorDownloadCookie() {
  const checkCookie = setInterval(() => {
    if (getCookie("fileDownload")) {
      clearInterval(checkCookie);
      deleteCookie("fileDownload");
      displayOverlay(false);
    }
  }, 500);
}
