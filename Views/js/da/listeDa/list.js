import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsTable } from "./tableHandler";
import { configAgenceService } from "../../dit/config/listDitConfig.js";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";
import { allowOnlyNumbers } from "../../magasin/utils/inputUtils.js";
import { baseUrl } from "../../utils/config.js";

document.addEventListener("DOMContentLoaded", function () {
  const designations = document.querySelectorAll(".designation-btn");
  designations.forEach((designation) => {
    designation.addEventListener("click", function () {
      let numeroLigne = this.getAttribute("data-numero-ligne");
      localStorage.setItem("currentTab", numeroLigne);
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

  /**==========================================================================================
   * Configuration sur le modal et le form dans le modal pour la demande de déverrouillage
   *===========================================================================================*/
  const deverouillageModal = document.getElementById(
    "demandeDeverouillageModal"
  );
  const modal = new bootstrap.Modal(deverouillageModal);

  deverouillageModal.addEventListener("show.bs.modal", function (event) {
    const triggerButton = event.relatedTarget; // ← voici le bouton qui a ouvert le modal
    const numeroDA = triggerButton.dataset.numeroDa; // Récupération du numéro de DA depuis l'attribut data-numero-da
    const idDA = triggerButton.dataset.idDa; // Récupération de l'ID de DA depuis l'attribut data-id-da
    deverouillageModal.querySelector(
      "#demandeDeverouillageModalLabel"
    ).textContent = `Demande de déverrouillage pour la DA n° ${numeroDA}`; // Mettre à jour le titre du modal avec le numéro de DA
    const form = deverouillageModal.querySelector("form");
    form.querySelector("#historique_modif_da_idDa").value = idDA; // Mettre à jour l'ID de DA dans le formulaire
    form.querySelector("textarea").value = ""; // Réinitialiser le champ de texte
  });

  deverouillageModal
    .querySelector("form")
    .addEventListener("submit", function (event) {
      event.preventDefault(); // Empêche l'envoi du formulaire par défaut
      const motif = this.querySelector("textarea").value;

      if (motif) {
        // Logique pour traiter le formulaire
        console.log(
          `Demande de déverrouillage pour la DA avec le motif: ${motif}`
        );
        modal.hide(); // Ferme le modal après traitement
        this.submit(); // Soumet le formulaire si nécessaire
      }
    });

  /**==================================================
   * Configuration du modal de confirmation
   *===================================================*/
  const confirmationModal = document.getElementById("confirmationModal");
  const modalConfirmation = new bootstrap.Modal(confirmationModal);

  confirmationModal.addEventListener("show.bs.modal", function (event) {
    const triggerButton = event.relatedTarget; // ← le bouton qui a ouvert le modal
    const numeroDA = triggerButton.dataset.numeroDa; // Récupération du numéro de DA depuis l'attribut data-numero-da
    const idDA = triggerButton.dataset.idDa; // Récupération de l'ID de DA depuis l'attribut data-id-da
    confirmationModal.querySelectorAll("numda").forEach((element) => {
      element.textContent = numeroDA; // Mettre à jour le numéro de DA dans le modal
    });
    confirmationModal
      .querySelector("#confirmActionBtn")
      .addEventListener("click", function () {
        // Logique pour traiter la confirmation
        window.location.href = `${baseUrl}/demande-appro/deverrouiller-da/${idDA}`; // Redirection vers l'URL de déverrouillage
        modalConfirmation.hide(); // Ferme le modal après traitement
      });
  });

  /**
   * Suppression de ligne de DA
   */
  const deleteLineBtns = document.querySelectorAll(".delete-line-DA");
  deleteLineBtns.forEach((deleteLineBtn) => {
    deleteLineBtn.addEventListener("click", function () {
      let dalId = this.dataset.id;
      Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        html: `Voulez-vous vraiment supprimer cette ligne d'article?<br><strong>Attention :</strong> cette action est <span style="color: red;"><strong>irréversible</strong></span>.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Non, annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          window.location = `${baseUrl}/demande-appro/delete-line-da/${dalId}`;
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
      menu.remove();
    }
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
