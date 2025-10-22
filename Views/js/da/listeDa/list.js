import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsTable } from "./tableHandler";
import { configAgenceService } from "../../dit/config/listDitConfig.js";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";
import { allowOnlyNumbers } from "../../magasin/utils/inputUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  const designations = document.querySelectorAll(".designation-btn");
  designations.forEach((designation) => {
    designation.addEventListener("click", function () {
      let numeroLigne = this.dataset.numeroLigne;
      let numeroDa = this.dataset.numeroDa;
      localStorage.setItem(`currentTab_${numeroDa}`, numeroLigne);
    });
  });
  mergeCellsTable(1); // fusionne le tableau en fonction de la colonne DA

  /**===========================================================================
   * Configuration des agences et services
   *============================================================================*/

  // Attachement des événements pour les agences
  configAgenceService.emetteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("emetteur")
  );

  configAgenceService.debiteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("debiteur")
  );

  /**==================================================
   * valider seulement les chiffres
   *===================================================*/

  const idMaterielInput = document.querySelector("#da_search_idMateriel");
  idMaterielInput.addEventListener("input", () =>
    allowOnlyNumbers(idMaterielInput)
  );

  /**
   * Suppression de ligne de DA
   */
  const deleteLineBtns = document.querySelectorAll(".delete-line-DA");
  deleteLineBtns.forEach((deleteLineBtn) => {
    deleteLineBtn.addEventListener("click", function () {
      let deletePath = this.dataset.deletePath;
      Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        html: `Voulez-vous vraiment supprimer cette ligne d'article?<br><strong>Attention :</strong> cette action est <span style="color: red;"><strong>irréversible</strong></span>.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Non, annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          window.location = deletePath;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulé",
            text: "La suppression de la ligne de demande a été annulée.",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      });
    });
  });

  /**
   * Demande de devis de ligne de DA
   */
  const demandeDevisBtns = document.querySelectorAll(".devis-demande");
  demandeDevisBtns.forEach((demandeDevisBtn) => {
    demandeDevisBtn.addEventListener("click", function () {
      let demandeDevisPath = this.dataset.demandeDevisPath;
      Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        html: `Voulez-vous confirmer l'envoi des demandes de devis aux fournisseurs ?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Oui, confirmer",
        cancelButtonText: "Non, abandonner",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          window.location = demandeDevisPath;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulation",
            text: "Opération abandonnée.",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      });
    });
  });

  /**
   * Désactiver l'ouverture du dropdown s'il n'y a pas d'enfant
   **/
  const dropdowns = document.querySelectorAll(".dropdown");

  dropdowns.forEach(function (dropdown) {
    const menu = dropdown.querySelector(".dropdown-menu");
    const button = dropdown.querySelector(".dropdown-toggle");

    if (menu && menu.children.length === 0 && button) {
      menu.classList.add("d-none"); // ou "hidden"
      button.disabled = true; // empêche l'interaction
    }
  });

  /**
   * Icônes de tri
   **/
  const sortIcons = document.querySelectorAll(".sort-icon");
  sortIcons.forEach((icon) => {
    icon.addEventListener("click", (e) => {
      e.preventDefault(); // Empêche le comportement par défaut du lien
      displayOverlay(true);
      let iconActif = icon.firstElementChild.classList.contains("text-warning");
      let urlObjet = new URL(icon.href); // Crée un objet URL pour faciliter la gestion des paramètres

      if (iconActif) {
        urlObjet.searchParams.delete("sort");
        urlObjet.searchParams.delete("direction");
      }

      window.location.href = urlObjet.toString(); // Redirige vers l'URL avec les nouveaux paramètres
    });
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
