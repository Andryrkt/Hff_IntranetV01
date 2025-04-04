import { fetchDataAgenceService } from "../../api/agenceServiceFetch.js";
import {
  configAgenceService,
  configDocSoumisDwModal,
} from "../config/listDitConfig.js";
import {
  supprimLesOptions,
  DeleteContentService,
} from "../../utils/ui/uiAgenceServiceUtils.js";
import { fetchDevis } from "../../api/devisFetch.js";
/**==================================================
 * Configuration des agences et services
 *===================================================*/
/**
 * Fonction pour gérer le changement d'agence (émetteur ou débiteur)
 * @param {string} configKey - La clé de configuration utilisée.
 */
export function handleAgenceChange(configKey) {
  const { agenceInput, serviceInput, spinner, container } =
    configAgenceService[configKey];
  const agence = agenceInput.value;

  // Efface les options si nécessaire, et sort si `agence` est vide
  if (DeleteContentService(agence, serviceInput)) {
    return;
  }

  // Appel à la fonction pour récupérer les données de l'agence
  fetchDataAgenceService(agence, serviceInput, spinner, container);
}

/**=======================================
 * Docs à intégrer dans DW MODAL
 * ======================================*/
/**
 * Gestionnaire pour l'événement d'ouverture du modal.
 * @param {Event} event - L'événement déclenché.
 */
export function docSoumisModalShow(event) {
  const {
    numeroDitInput,
    numDitHiddenInput,
    spinnerSelect,
    selectContainer,
    selecteInput,
  } = configDocSoumisDwModal;

  const button = event.relatedTarget;
  const numDit = button.getAttribute("data-id");

  // Récupère les données associées au numéro DIT
  fetchDevis(numDit, spinnerSelect, selectContainer, selecteInput);

  // Met à jour les valeurs des champs associés
  numeroDitInput.innerHTML = numDit;
  numDitHiddenInput.value = numDit;
}

/**
 * Gestionnaire pour l'événement de fermeture du modal.
 */
export function docSoumisModalHidden() {
  const { selecteInput } = configDocSoumisDwModal;

  // Supprime les options du select
  supprimLesOptions(selecteInput);
}
