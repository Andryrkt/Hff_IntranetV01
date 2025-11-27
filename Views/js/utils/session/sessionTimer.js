import { afficherToast } from "../toastUtils";

export function initSessionTimer({
  duration = 900,
  logoutUrl,
  chronoTextSelector = "#chrono-text",
  chronoProgressSelector = ".chrono-progress",
}) {
  let timeRemaining = duration;
  let timer = null;
  let logoutTimeout = null;

  const chronoText = document.querySelector(chronoTextSelector);
  const chronoProgress = document.querySelector(chronoProgressSelector);

  const updateDisplay = () => {
    if (!chronoText || !chronoProgress) return;

    const progress = (timeRemaining / duration) * 100;
    chronoProgress.style.width = `${progress}%`;

    chronoProgress.style.backgroundColor =
      progress > 50 ? "#4caf50" : progress > 20 ? "#ff9800" : "#f44336";

    const min = String(Math.floor(timeRemaining / 60)).padStart(2, "0");
    const sec = String(timeRemaining % 60).padStart(2, "0");
    chronoText.textContent = `${min}:${sec}`;
  };

  const tick = () => {
    timeRemaining--;
    updateDisplay();

    if (timeRemaining <= 0) {
      stopAll();
      window.location.href = logoutUrl;
      return;
    }

    if (timeRemaining <= 15) {
      afficherToast("erreur", `Votre session expire dans ${timeRemaining}s`);
    }
  };

  const reset = () => {
    stopAll();
    timeRemaining = duration;
    updateDisplay();

    localStorage.setItem("session-active", Date.now().toString());

    timer = setInterval(tick, 1000);
    logoutTimeout = setTimeout(() => {
      window.location.href = logoutUrl;
    }, duration * 1000);
  };

  const stopAll = () => {
    clearInterval(timer);
    clearTimeout(logoutTimeout);
  };

  // Sync onglets
  window.addEventListener("storage", (e) => {
    if (e.key === "session-active") reset();
  });

  // Activité utilisateur
  ["mousemove", "keypress", "click", "scroll", "touchstart"].forEach((ev) =>
    window.addEventListener(ev, reset)
  );

  // Vérification background (fallback)
  setInterval(() => {
    const lastActive = Number(localStorage.getItem("session-active"));
    if (Date.now() - lastActive > duration * 1000) {
      window.location.href = logoutUrl;
    }
  }, 10000);

  reset(); // démarre tout
}
