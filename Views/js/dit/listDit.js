import { TableauComponent } from "../Component/TableauComponent.js";
import {
  configAgenceService,
  configDocSoumisDwModal,
  configCloturDit,
} from "./config/listDitConfig.js";
import {
  handleAgenceChange,
  docSoumisModalHidden,
  docSoumisModalShow,
} from "./fonctionUtils/fonctionListDit.js";
import {
  toUppercase,
  allowOnlyNumbers,
  limitInputLength,
} from "../utils/inputUtils.js";
import {
  toggleSpinner,
  affichageOverlay,
  affichageSpinner,
} from "../utils/ui/uiSpinnerUtils.js";
document.addEventListener("DOMContentLoaded", (event) => {
  /**===========================================================================
   * Configuration des agences et services
   *===========================================================================*/

  // Attachement des événements pour les agences
  configAgenceService.emetteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("emetteur")
  );

  configAgenceService.debiteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("debiteur")
  );

  /**=======================================
   * Docs à intégrer dans DW MODAL
   * ======================================*/

  configDocSoumisDwModal.docDansDwModal.addEventListener(
    "show.bs.modal",
    docSoumisModalShow
  );

  // Gestionnaire pour la fermeture du modal
  configDocSoumisDwModal.docDansDwModal.addEventListener(
    "hidden.bs.modal",
    docSoumisModalHidden
  );

  /**====================================================
   * MISE EN MAJUSCULE
   *=================================================*/
  const numDitSearchInput = document.querySelector("#dit_search_numDit");
  numDitSearchInput.addEventListener("input", () => {
    toUppercase(numDitSearchInput);
    limitInputLength(numDitSearchInput, 11);
  });

  /**===========================================
   * SEULMENT DES CHIFFRES
   *============================================*/
  const numOrSearchInput = document.querySelector("#dit_search_numOr");
  const numDevisSearchInput = document.querySelector("#dit_search_numDevis");
  numOrSearchInput.addEventListener("input", () => {
    allowOnlyNumbers(numOrSearchInput);
    limitInputLength(numOrSearchInput, 8);
  });
  numDevisSearchInput.addEventListener("input", () => {
    allowOnlyNumbers(numDevisSearchInput);
    limitInputLength(numDevisSearchInput, 8);
  });

  allowOnlyNumbers(numDevisSearchInput);

  /**==================================================
   * sweetalert pour le bouton cloturer dit
   *==================================================*/

  configCloturDit.clotureDit.forEach((el) => {
    el.addEventListener("click", (e) => {
      e.preventDefault();
      let id = el.getAttribute("data-id");

      Swal.fire(configCloturDit.text).then((result) => {
        if (result.isConfirmed) {
          // Afficher un overlay de chargement
          affichageOverlay();

          // Ajouter un spinner CSS
          affichageSpinner();

          // Redirection après confirmation
          window.location.href = `/Hffintranet/cloturer-annuler/${id}`;
        }
      });
    });
  });

  /**======================
   * LIST COMMANDE MODAL
   * ======================*/
  const listeCommandeModal = document.getElementById("listeCommande");
  const loading = document.getElementById("loading");
  const dataContent = document.getElementById("dataContent");

  listeCommandeModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const id = button.getAttribute("data-id"); // Extract info from data-* attributes

    // Afficher le spinner et masquer le contenu des données
    toggleSpinner(loading, dataContent, true);

    // Fetch request to get the data
    fetch(`/Hffintranet/command-modal/${id}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        const tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML = ""; // Clear previous data

        if (data.length > 0) {
          data.forEach((command) => {
            let typeCommand;
            if (command.slor_typcf == "ST" || command.slor_typcf == "LOC") {
              typeCommand = "Local";
            } else if (command.slor_typcf == "CIS") {
              typeCommand = "Agence";
            } else {
              typeCommand = "Import";
            }

            // Formater la date
            const date = new Date(command.fcde_date);
            const formattedDate = `${date
              .getDate()
              .toString()
              .padStart(2, "0")}/${(date.getMonth() + 1)
              .toString()
              .padStart(2, "0")}/${date.getFullYear()}`;

            // Affichage
            let row = `<tr>
                    <td>${command.slor_numcf}</td> 
                    <td>${formattedDate}</td>
                    <td> ${typeCommand}</td>
                    <td> ${command.fcde_posc}</td>
                    <td> ${command.fcde_posl}</td>
                </tr>`;
            tableBody.innerHTML += row;
          });
        } else {
          // Si les données sont vides, afficher un message vide
          tableBody.innerHTML =
            '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
        }
      })
      .catch((error) => {
        const tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML =
          '<tr><td colspan="5">Could not retrieve data.</td></tr>';
        console.error("There was a problem with the fetch operation:", error);
      })
      .finally(() => toggleSpinner(loading, dataContent, false));
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    const tableBody = document.getElementById("commandesTableBody");
    tableBody.innerHTML = ""; // Vider le tableau
  });
});
/**
 * CREATION D'EXCEL
 */
// const typeDocumentInput = document.querySelector("#dit_search_typeDocument");
// const niveauUrgenceInput = document.querySelector("#dit_search_niveauUrgence");
// const statutInput = document.querySelector("#dit_search_statut");
// const idMaterielInput = document.querySelector("#dit_search_idMateriel");
// const interExternInput = document.querySelector("#dit_search_internetExterne");
// const dateDemandeDebutInput = document.querySelector("#dit_search_dateDebut");
// const dateDemandeFinInput = document.querySelector("#dit_search_dateFin");
// const buttonExcelInput = document.querySelector("#excelDit");
// buttonExcelInput.addEventListener("click", recherche);

// function recherche() {
//   const typeDocument = typeDocumentInput.value;
//   const niveauUrgence = niveauUrgenceInput.value;
//   const statut = statutInput.value;
//   const idMateriel = idMaterielInput.value;
//   const interExtern = interExternInput.value;
//   const dateDemandeDebut = dateDemandeDebutInput.value;
//   const dateDemandeFin = dateDemandeFinInput.value;

//   let url = "/Hffintranet/dit-excel";

//   const data = {
//     idMateriel: idMateriel || null,
//     typeDocument: typeDocument || null,
//     niveauUrgence: niveauUrgence || null,
//     statut: statut || null,
//     interExtern: interExtern || null,
//     dateDebut: dateDemandeDebut || null,
//     dateFin: dateDemandeFin || null,
//   };

//   fetch(url, {
//     method: "POST",
//     headers: {
//       "Content-Type": "application/json",
//     },
//     body: JSON.stringify(data),
//   })
//     .then((response) => response.json())
//     .then((data) => {
//       console.log(data);
//     })
//     .catch((error) => {
//       console.error("Error:", error);
//     });
// }
