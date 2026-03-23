document.addEventListener("DOMContentLoaded", function () {
  const societeSelect = document.querySelector("#choix_societe_societe");
  const profilWrapper = document.querySelector("#choix-profil");
  const profilSelect = document.querySelector("#choix_societe_profil");

  // Stockage des options par société
  const profilsOptions = {};
  Array.from(profilSelect.options).forEach((option) => {
    const societeId = option.dataset.societe || "none";
    if (!profilsOptions[societeId]) profilsOptions[societeId] = [];
    profilsOptions[societeId].push(option.cloneNode(true));
  });

  // Placeholder commun
  const placeholder = document.createElement("option");
  placeholder.value = "";
  placeholder.textContent = "-- Choix du profil --";

  function populateProfils(societeId) {
    // reset
    profilSelect.innerHTML = "";
    profilSelect.appendChild(placeholder.cloneNode(true));

    const options = profilsOptions[societeId] || [];

    options.forEach((option) => {
      profilSelect.appendChild(option.cloneNode(true));
    });

    // ✅ logique d'affichage automatique
    const availableOptions = profilSelect.querySelectorAll(
      'option:not([value=""])'
    );
    if (availableOptions.length > 1) {
      profilWrapper.classList.remove("d-none"); // afficher
      profilSelect.value = ""; // pas de sélection par défaut
    } else if (availableOptions.length === 1) {
      profilWrapper.classList.add("d-none"); // cacher
      profilSelect.value = availableOptions[0].value; // sélection par défaut
    } else {
      profilWrapper.classList.add("d-none"); // rien à choisir
      profilSelect.value = "";
    }
  }

  // écoute du choix société
  societeSelect.addEventListener("change", function () {
    populateProfils(this.value);
  });
});
