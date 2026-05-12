import { displayOverlay } from "../../utils/ui/overlay";
import { mergeCellsRecursiveTable } from "../listeCdeFrn/tableHandler.js";
import { configAgenceService } from "../../dit/config/listDitConfig.js";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";
import { allowOnlyNumbers } from "../../magasin/utils/inputUtils.js";
import { initCentraleCodeDesiInputs } from "../newReappro/event.js";
import { FetchManager } from "../../api/FetchManager.js";
const fetchManager = new FetchManager();


document.addEventListener("DOMContentLoaded", function () {
  initCentraleCodeDesiInputs(
    "da_search_codeCentrale",
    "da_search_desiCentrale",
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
    { pivotIndex: 1, columns: [1], insertSeparator: true },
    { pivotIndex: 2, columns: [0, 2, 3, 4, 5, 6, 7, 8], insertSeparator: true },
    { pivotIndex: 12, columns: [12, 26], insertSeparator: true },
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
    allowOnlyNumbers(idMaterielInput),
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

  /**
   * Evenement sur "Afficher les DA à traiter" pour filtrer les statuts
   **/
  const checkboxAfficherTraiter = document.getElementById("da_search_afficherDaTraiter");
  const selectStatutDA = document.getElementById("da_search_statutDA");
  const selectStatutBC = document.getElementById("da_search_statutBC");

  if (checkboxAfficherTraiter && selectStatutDA && selectStatutBC) {
    const isApproUser = checkboxAfficherTraiter.dataset.isApproUser === "1";

    const filterChoices = () => {
      const isChecked = checkboxAfficherTraiter.checked;

      [selectStatutDA, selectStatutBC].forEach((select) => {
        Array.from(select.options).forEach((option) => {
          if (option.value === "") return; // Garder le placeholder

          if (isChecked) {
            let keep = false;
            if (isApproUser) {
              keep = option.dataset.traiterAppro === "1";
            } else {
              if (select === selectStatutDA) {
                keep = option.dataset.traiterPasAppro === "1";
              } else {
                keep = false;
              }
            }

            if (!keep) {
              option.style.display = "none";
              option.disabled = true;
            } else {
              option.style.display = "";
              option.disabled = false;
            }
          } else {
            option.style.display = "";
            option.disabled = false;
          }
        });

        // Si l'option actuellement sélectionnée est maintenant cachée, on reset le select
        if (select.selectedOptions[0] && select.selectedOptions[0].style.display === "none") {
          select.value = "";
        }
      });
    };

    checkboxAfficherTraiter.addEventListener("change", filterChoices);
    filterChoices(); // Appel initial au chargement
  }
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

      if (dateActuelle != "N/A") {
        const [day, month, year] = dateActuelle.split("/");
        const formatted = `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;

        // Pré-rempli le champ de date dans le formulaire du modal
        const dateInput = modalDateLivraison.querySelector(
          "#da_modal_date_livraison_dateLivraisonPrevue",
        );
        if (dateInput) {
          dateInput.value = formatted;
        }
      }

      // Mise à jour du contenu du modal
      const modalTitle = modalDateLivraison.querySelector(".modal-title");
      if (modalTitle) {
        modalTitle.textContent =
          "Modifier la date de livraison pour la commande n° : " + numeroCde;
      }

      // remplir le champ cacher avec le numero commande
      const numeroCdeInput = modalDateLivraison.querySelector(
        "#da_modal_date_livraison_numeroCde",
      );
      if (numeroCdeInput) {
        numeroCdeInput.value = numeroCde;
      }
    });
  }
});


/** ===================================================
 * MODAL de clôture de DDP
 *==================================================*/
// Attendre que le DOM soit entièrement chargé
document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner le modal par son ID
  const modalDdpCloture = document.getElementById("ddpCloture");

  // Verifier si le modal existe sur la page
  if (modalDdpCloture) {
    //Ecouter l'événement 'show.bs.modal' qui est déclenché par Bootstrap
    // juste avant que le modal se soit affiché.
    modalDdpCloture.addEventListener("show.bs.modal", function (event) {
      // event.relatedTarget est l'élément qui a déclenché le modal (notre lien <a>)
      const button = event.relatedTarget;

      // Récupérer les données depuis les attributs data-* du lien
      const numeroCde = button.getAttribute("data-numero-cde");
      const numeroDa = button.getAttribute("data-numero-da");

      // Récupérer les données pour remplir le corps du tableau modal
      const modalBody = modalDdpCloture.querySelector("#statutClotureBody");
      modalBody.innerHTML = `
        <tr>
          <td colspan="3" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
          </td>
        </tr>`;

      fetchManager
        .get(`api/statut-compta/${numeroDa}/${numeroCde}`)
        .then((data) => {
          modalBody.innerHTML = data
            .map(
              (item) => {
                let styleStatut = "statut-" + transformerPhrase(item.statut);
                return `
                        <tr>
                            <td>${item.date_soumission}</td>
                            <td>${item.numero}</td>
                            <td>${item.type}</td>
                            <td>${item.motif || '-'}</td>
                            <td>${item.ratio_deja_paye}%</td>
                            <td class="text-end">${item.montant_ht}</td>
                            <td class="${styleStatut}">${item.statut}</td>
                        </tr>
                    `;
              }
            )
            .join("");
        })
        .catch(error => {
          console.error("Erreur:", error);
          modalBody.innerHTML = `<tr><td colspan="6" class="text-center">Erreur de chargement</td></tr>`;
        });
    });
  }
});
function transformerPhrase(phrase) {
  if (!phrase || typeof phrase !== 'string') {
    return '';
  }

  // 1. Enlever les accents
  const sansAccents = phrase.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  // 2. Mettre en minuscules et remplacer les espaces par des tirets
  return sansAccents.toLowerCase().trim().replace(/\s+/g, '-');

}
