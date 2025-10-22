export function displayOverlay(afficher) {
  const overlay = document.getElementById("loading-overlay");
  if (afficher) {
    overlay.style.display = "flex";
  } else {
    overlay.style.display = "none";
  }
}
