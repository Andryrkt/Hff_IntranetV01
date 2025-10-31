import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";

/**===========================================================================
 * Configuration des agences et services
 *============================================================================*/

// Attachement des événements pour les agences
document
  .getElementById("reporting_ips_search_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
