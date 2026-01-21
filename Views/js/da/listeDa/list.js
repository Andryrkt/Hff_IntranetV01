import { displayOverlay } from "../../utils/ui/overlay";
import { mergeCellsRecursiveTable } from "../listeCdeFrn/tableHandler.js";
import { configAgenceService } from "../../dit/config/listDitConfig.js";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";
import { allowOnlyNumbers } from "../../magasin/utils/inputUtils.js";
import { initCentraleCodeDesiInputs } from "../newReappro/event.js";

document.addEventListener("DOMContentLoaded", function () {
  initCentraleCodeDesiInputs(
    "da_search_codeCentrale",
    "da_search_desiCentrale"
  );
  const designations = document.querySelectorAll(".designation-btn");
  designations.forEach((designation) => {
    designation.addEventListener("click", function () {
      let numeroLigne = this.dataset.numeroLigne;
      let numeroDa = this.dataset.numeroDa;
      localStorage.setItem(`currentTab_${numeroDa}`, numeroLigne);
    });
  });
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0], insertSeparator: true },
    { pivotIndex: 2, columns: [1, 2, 3, 4, 5, 6, 7], insertSeparator: true },
  ]);

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

  /**
   * Evenement sur type de DA dans le formulaire de recherche
   **/
  const typeDaSelect = document.getElementById("da_search_typeAchat");
  const desiCentraleInput = document.getElementById("da_search_desiCentrale");
  const inputDesiCentraleGroup = desiCentraleInput.parentElement;
  typeDaSelect.addEventListener("change", function () {
    if (inputDesiCentraleGroup.dataset.afficherInput != 1) return;

    let daReappro = this.value == 2;
    let divContainer = inputDesiCentraleGroup.parentElement;
    let editIcon = document.getElementById("editIcon");

    if (daReappro) {
      divContainer.classList.remove("d-none");
      desiCentraleInput.disabled = false;
      inputDesiCentraleGroup.classList.remove("input-group");
      editIcon.classList.add("d-none");
      desiCentraleInput.focus();
    } else {
      divContainer.classList.add("d-none");
    }
  });
});

/** ===================================================
 * Modal du Date livraison prevu
 *==================================================*/
// Attendre que le DOM soit entièrement chargé
document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner le modal par son ID
  const modalDateLivraison = document.getElementById("dateLivraison");

  // Verifier si le modal existe sur la page
  if (modalDateLivraison) {
    //Ecouter l'événement 'show.bs.modal' qui est déclenché par Bootstrap
    // juste avant que le modal se soit affiché.
    modalDateLivraison.addEventListener("show.bs.modal", function (event) {
      // event.relatedTarget est l'élément qui a déclenché le modal (notre lien <a>)
      const button = event.relatedTarget;

      // Récupérer les données depuis les attributs data-* du lien
      const numeroCde = button.getAttribute("data-numero-cde");
      const dateActuelle = button.getAttribute("data-date-actuelle");

      // Mise à jour du contenu du modal
      const modalTitle = modalDateLivraison.querySelector(".modal-title");
      if (modalTitle) {
        modalTitle.textContent =
          "Modifier la date de livraison pour la commande n° : " + numeroCde;
      }

      // Pré-rempli le champ de date dans le formulaire du modal
      const dateInput = modalDateLivraison.querySelector(
        "#da_modal_date_livraison_dateLivraisonPrevue"
      );

      if (dateInput) {
        dateInput.value = dateActuelle;
      }

      // remplir le champ cacher avec le numero commande
      const numeroCdeInput = modalDateLivraison.querySelector(
        "#da_modal_date_livraison_numeroCde"
      );
      if (numeroCdeInput) {
        numeroCdeInput.value = numeroCde;
      }
    });
  }
});
