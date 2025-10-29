export function displayOverlay(afficher, message = "") {
  const overlay = document.getElementById("loading-overlay");
  const textOverlay = overlay.querySelector(".text-overlay");
  overlay.style.display = afficher ? "flex" : "none";
  textOverlay.textContent = message || "Veuillez patienter s'il vous plaît!";
}
