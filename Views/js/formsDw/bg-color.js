import { displayOverlay } from "../utils/ui/overlay";

const iframe = document.getElementById("iframeDW");

// fallback si l'iframe met trop longtemps
const timeout = setTimeout(() => {
  displayOverlay(false);
}, 15000); // 15 secondes max

iframe.addEventListener("load", () => {
  // ignorer about:blank ou src vide
  if (!iframe.src || iframe.src === "about:blank") return;

  clearTimeout(timeout);
  // optionnel : léger délai pour s'assurer que tout est rendu
  setTimeout(() => {
    displayOverlay(false);
  }, 3000); // 3s
});

document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeau = document.querySelector("#bandeau");
  if (content) content.classList.add(bandeau.dataset.bgColor);
  if (bandeau) bandeau.classList.add(bandeau.dataset.bgColor);
});
