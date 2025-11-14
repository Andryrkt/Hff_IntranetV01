import { handleAgenceChange } from "../dit/fonctionUtils/fonctionListDit.js";
/**===========================================================================
 * Configuration des agences et services
 *============================================================================*/

// Attachement des événements pour les agences emetteur
document
  .getElementById("bon_de_caisse_emetteur_agence")
  .addEventListener("change", () => handleAgenceChange("emetteur"));

// Attachement des événements pour les agences debiteur
document
  .getElementById("bon_de_caisse_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
