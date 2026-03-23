export function filterServiceByAgence({
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

  const allServiceOptions = Array.from(serviceSelect.options).filter(
    (opt) => opt.value !== ""
  );

  function clearService() {
    while (serviceSelect.options.length > 1) {
      serviceSelect.remove(1);
    }
    serviceSelect.value = "";
  }

  function filterServices(agenceId) {
    clearService();
    if (!agenceId) return;
    allServiceOptions.forEach((opt) => {
      if (opt.dataset.agence === String(agenceId)) {
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
