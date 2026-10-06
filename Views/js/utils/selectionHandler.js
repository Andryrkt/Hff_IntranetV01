import { fetchData } from "./fetchUtils.js";
import { resetDropdown, populateDropdown } from "./dropdownUtils.js";
import { toggleSpinner } from "./spinnerUtils.js";

export async function updateDropdown(
  dropdown,
  url,
  defaultText,
  spinnerElement,
  containerElement,
  values = ""
) {
  try {
    // Affiche le spinner avant de lancer le fetch
    toggleSpinner(spinnerElement, containerElement, true);

    const data = await fetchData(url); // Appelle fetchData pour récupérer les données

    resetDropdown(dropdown, defaultText); // Réinitialise le dropdown
    populateDropdown(dropdown, data); // Ajoute les nouvelles options

    if (values) {
      dropdown.value = values; // Définit la valeur sélectionnée si fournie
    }
  } catch (error) {
    console.error("Erreur lors de la mise à jour du dropdown:", error);
  } finally {
    // Désactive le spinner une fois le traitement terminé
    toggleSpinner(spinnerElement, containerElement, false);
  }
}
