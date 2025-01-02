import { TableauComponent } from "../Component/TableauComponent.js";
import { fetchDataAgenceService } from "../api/agenceServiceFetch.js";
import { configAgenceService } from "./config/listDitConfig.js";
import {
  toUppercase,
  allowOnlyNumbers,
  limitInputLength,
} from "../utils/inputUtils.js";
import {
  supprimLesOptions,
  DeleteContentService,
  updateServiceOptions,
} from "../utils/ui/uiAgenceServiceUtils.js";
import { toggleSpinner } from "../utils/ui/uiSpinnerUtils.js";

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

/**
 * Fonction pour gérer le changement d'agence (émetteur ou débiteur)
 * @param {string} configKey - La clé de configuration utilisée.
 */
function handleAgenceChange(configKey) {
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

document.addEventListener("DOMContentLoaded", (event) => {
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

  /**=======================================
   * Docs à intégrer dans DW MODAL
   * ======================================*/

  const docDansDwModal = document.getElementById("docDansDw");
  const numeroDitInput = document.querySelector("#numeroDit");
  const numDitHiddenInput = document.querySelector("#doc_dans_dw_numeroDit");
  const selecteInput = document.querySelector("#doc_dans_dw_docDansDW");
  const spinnerSelect = document.getElementById("spinner-doc-soumis");
  const selectContainer = document.getElementById("container-doc-soumis");

  docDansDwModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const numDit = button.getAttribute("data-id");
    recupDonnerDevis(numDit);
    numeroDitInput.innerHTML = numDit;
    numDitHiddenInput.value = numDit;
  });

  // Gestionnaire pour la fermeture du modal
  docDansDwModal.addEventListener("hidden.bs.modal", function () {
    supprimLesOptions(selecteInput);
  });

  function recupDonnerDevis(numDit) {
    const url = `/Hffintranet/constraint-soumission/${numDit}`;
    toggleSpinner(spinnerSelect, selectContainer, true);
    fetch(url)
      .then((response) => response.json())
      .then((docDansDw) => {
        console.log(docDansDw);
        let docASoumettre = valeurDocASoumettre(docDansDw);
        updateServiceOptions(docASoumettre, selecteInput);
      })
      .catch((error) => console.error("Error:", error))
      .finally(() => toggleSpinner(spinnerSelect, selectContainer, false));
  }

  /**
   * Détermine les documents à soumettre en fonction des conditions.
   * @param {Object} docDansDw - L'objet contenant les informations nécessaires.
   * @returns {Array} - Un tableau d'objets avec `value` et `text`.
   */
  function valeurDocASoumettre(docDansDw) {
    let docASoumettre = [];

    if (
      docDansDw.client === "EXTERNE" &&
      docDansDw.statutDit === "AFFECTEE SECTION" &&
      docDansDw.statutDevis !== "Validé"
    ) {
      docASoumettre = [{ value: "DEVIS", text: "DEVIS" }];
    } else if (
      docDansDw.client === "EXTERNE" &&
      docDansDw.statutDevis === "Validé"
    ) {
      docASoumettre = [
        { value: "DEVIS", text: "DEVIS" },
        { value: "BC", text: "BC" },
      ];
    } else {
      docASoumettre = [
        { value: "OR", text: "OR" },
        { value: "RI", text: "RI" },
        { value: "FACTURE", text: "FACTURE" },
      ];
    }

    return docASoumettre; // Retourne le tableau
  }
});

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
const clotureDit = document.querySelectorAll(".clotureDit");

clotureDit.forEach((el) => {
  el.addEventListener("click", (e) => {
    e.preventDefault();
    let id = el.getAttribute("data-id");

    Swal.fire({
      title: "Êtes-vous sûr ?",
      text: "Cette action est irréversible",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "OUI",
    }).then((result) => {
      if (result.isConfirmed) {
        // Afficher un overlay de chargement
        const overlay = document.createElement("div");
        overlay.style.position = "fixed";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
        overlay.style.zIndex = "9999";
        overlay.style.display = "flex";
        overlay.style.alignItems = "center";
        overlay.style.justifyContent = "center";
        overlay.innerHTML = `
          <div class="spinner"></div>
        `;
        document.body.appendChild(overlay);

        // Ajouter un spinner CSS
        const style = document.createElement("style");
        style.innerHTML = `
          .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid rgb(219, 188, 52);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
          }
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `;
        document.head.appendChild(style);

        // Redirection après confirmation
        window.location.href = `/Hffintranet/cloturer-annuler/${id}`;
      }
    });
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
