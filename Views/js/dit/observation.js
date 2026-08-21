import { displayOverlay } from "../utils/ui/overlay";

window.addEventListener("load", () => {
  const conversationContainer = document.getElementById(
    "conversationContainer"
  );

  if (!conversationContainer) return;

  const interval = setInterval(() => {
    const firstChild = conversationContainer.firstElementChild;

    if (firstChild && firstChild.offsetHeight > 0) {
      // Le contenu est prêt, on peut scroller en bas
      conversationContainer.scrollTop = conversationContainer.scrollHeight;

      // Stoppe le setInterval
      clearInterval(interval);
    }
  }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
  // ===================================================
  // GESTION DU TEXTAREA AUTO-RESIZE ET ENVOI FORMULAIRE
  // ===================================================
  const messageInput = document.getElementById("dit_observation_observation");

  if (messageInput) {
    // Auto-resize du textarea
    messageInput.addEventListener("input", function () {
      this.style.height = "auto";
      this.style.height = Math.min(this.scrollHeight, 120) + "px";
    });

    const form = messageInput.closest("form");
    if (form) {
      form.addEventListener("submit", function () {
        displayOverlay(true, "Envoi de l'observation en cours...");
        setTimeout(() => {
          messageInput.style.height = "auto";
          messageInput.value = "";
        }, 100);
      });
    }
  }
});
