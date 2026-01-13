import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
const fetchManager = new FetchManager();

/**===================================================
 * Autocomplete champ FOURNISSEUR
 *====================================================*/
const fournisseurInput = document.querySelector("#bon_apayer_fournisseur");

async function fetchFournisseurs() {
  return await fetchManager.get("api/numero-libelle-fournisseur");
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

function onSelectNumFournisseur(item) {
  fournisseurInput.value = `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: fournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-fournisseur"),
  loaderElement: document.querySelector("#loader-fournisseur"),
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNumFournisseur,
});

/**============================================
 * Bouton TRANSMETTRE BAP
 *============================================*/

document.addEventListener("DOMContentLoaded", () => {
  const transmettreBAPButton = document.getElementById("transmettreBAP");

  if (transmettreBAPButton) {
    transmettreBAPButton.addEventListener("click", async () => {
      const checkboxes = document.querySelectorAll(".bap-checkbox");
      const checkedBoxes = Array.from(checkboxes).filter((cb) => cb.checked);

      if (checkedBoxes.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Aucune sélection",
          text: "Veuillez sélectionner au moins une demande BAP à transmettre.",
        });
        return;
      }
      const selectedBAPs = checkedBoxes.map((cb) => cb.name);

      const confirmation = await Swal.fire({
        title: "Confirmer la transmission",
        text: `Êtes-vous sûr de vouloir transmettre ${selectedBAPs.length} demande(s) BAP à la comptabilité ?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Oui, transmettre",
        cancelButtonText: "Annuler",
      });

      if (confirmation.isConfirmed) {
        try {
          displayOverlay(
            true,
            "Transmission des demandes BAP en cours, merci de patienter ..."
          );
          const response = await fetchManager.post(
            "api/transmettre-bap-compta",
            {
              bapNumbers: selectedBAPs,
            }
          );
          displayOverlay(false);

          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Transmission réussie",
              text:
                response.message ||
                "Les demandes BAP ont été transmises avec succès.",
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Erreur lors de la transmission",
              text:
                response.message ||
                "Une erreur est survenue lors de la transmission des demandes BAP.",
            });
          }
        } catch (error) {
          displayOverlay(false);
          console.error(
            "Erreur lors de la transmission des demandes BAP :",
            error
          );
          Swal.fire({
            icon: "error",
            title: "Erreur réseau",
            text: "Une erreur réseau est survenue. Veuillez réessayer plus tard.",
          });
        }
      }
    });
  }

  /**============================================
   * Modal affichage PDF BAP
   *============================================*/
  const pdfModalElement = document.getElementById("pdfModal");
  if (pdfModalElement) {
    const pdfModal = new bootstrap.Modal(pdfModalElement);
    const iframe = pdfModalElement.querySelector("iframe");

    document.querySelectorAll(".show-pdf-modal").forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const pdfUrl = button.dataset.pdfUrl;
        if (pdfUrl && iframe) {
          // Ajout d'un paramètre pour forcer le rafraîchissement (cache busting)
          iframe.src = pdfUrl + '?v=' + new Date().getTime();
          pdfModal.show();
        }
      });
    });

    pdfModalElement.addEventListener("hidden.bs.modal", () => {
      if (iframe) {
        iframe.src = "";
      }
    });
  }
});