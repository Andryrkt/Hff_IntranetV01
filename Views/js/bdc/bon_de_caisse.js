import { filterServiceByAgence } from "../utils/agenceService/filterServiceByAgence.js";

document.addEventListener("DOMContentLoaded", () => {
  /**===========================================================================
   * Configuration des agences et services
   *============================================================================*/
  filterServiceByAgence({
    agenceSelector: "#bon_de_caisse_agenceEmetteur",
    serviceSelector: "#bon_de_caisse_serviceEmetteur",
  });

  filterServiceByAgence({
    agenceSelector: "#bon_de_caisse_agenceDebiteur",
    serviceSelector: "#bon_de_caisse_serviceDebiteur",
  });
});
