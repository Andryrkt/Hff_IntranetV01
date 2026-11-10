import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { ApiRequestManager } from "../../api/ApiRequestManager";
import { displayOverlay } from "../../utils/ui/overlay";
import { swalOptions } from "./ui/swalUtils";

/**
 * Suppression d'une ligne de DA (liste et détail) : confirmation, appel POST JSON, puis rechargement.
 * @param {HTMLElement} button bouton portant data-num-da et data-ligne
 */
export async function deleteLigneDa(button) {
  const { numDa, ligne } = button.dataset;
  const fetchManager = new ApiRequestManager();

  const confirmation = await Swal.fire({
    title: "Êtes-vous sûr(e) ?",
    html: `Voulez-vous vraiment supprimer cette ligne d'article?<br><strong>Attention :</strong> cette action est <span style="color: red;"><strong>irréversible</strong></span>.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Non, annuler",
  });

  if (!confirmation.isConfirmed) {
    Swal.fire(swalOptions.annulationOperation);
    return;
  }

  try {
    displayOverlay(true, "Suppression de la ligne en cours, merci de patienter ...");
    const result = await fetchManager.post(
      API_ENDPOINTS.deleteLigneDa(numDa, ligne),
      {}
    );
    displayOverlay(false);

    await Swal.fire(swalOptions.genericResponse(result));
    if (result.status === "success") {
      displayOverlay(true, "Action réussie ! La page se met à jour ... ");
      window.location.reload();
    }
  } catch (error) {
    displayOverlay(false);
    console.error(error);
    Swal.fire(swalOptions.errorGeneric(error));
  }
}
