export default function filterServiceByAgence({
  agenceSelector = "#agenceEmetteur",
  serviceSelector = "#serviceEmetteur",
} = {}) {
  const agenceSelect = document.querySelector(agenceSelector);
  const serviceSelect = document.querySelector(serviceSelector);

  if (!agenceSelect || !serviceSelect) {
    console.warn("filterServiceByAgence : sélecteur introuvable.", {
      agenceSelector,
      serviceSelector,
    });
    return;
  }

  // Snapshot de toutes les options service (hors placeholder)
  const allServiceOptions = Array.from(serviceSelect.options).filter(
    (opt) => opt.value !== ""
  );

  function resetService() {
    // Remettre le placeholder sélectionné
    serviceSelect.value = "";
  }

  function filterServices(agenceId) {
    // Reset d'abord
    resetService();

    // Vider puis reconstruire
    // On garde le placeholder (premier enfant)
    while (serviceSelect.options.length > 1) {
      serviceSelect.remove(1);
    }

    allServiceOptions.forEach((opt) => {
      if (!agenceId || opt.dataset.agence === String(agenceId)) {
        serviceSelect.appendChild(opt.cloneNode(true));
      }
    });
  }

  // Init au chargement : filtrer selon la valeur déjà sélectionnée
  filterServices(agenceSelect.value);

  agenceSelect.addEventListener("change", function () {
    filterServices(this.value);
  });
}
