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
