import { filterServiceByAgence } from "../utils/agenceService/filterServiceByAgence.js";

document.addEventListener("DOMContentLoaded", () => {
  filterServiceByAgence({
    agenceSelector: "#badm_search_agenceEmetteur",
    serviceSelector: "#badm_search_serviceEmetteur",
  });

  filterServiceByAgence({
    agenceSelector: "#badm_search_agenceDebiteur",
    serviceSelector: "#badm_search_serviceDebiteur",
  });
});
