import { TableauComponent } from "../Component/TableauComponent.js";
/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
// Configuration centralisée
const config = {
  elements: {
    agenceDebiteurInput: "#planning_search_agenceDebite",
    serviceDebiteurInput: "#planning_search_serviceDebite",
    selectAllCheckbox: "#planning_search_selectAll",
    searchForm: "#planning_search_form", // Ajout de l'ID du formulaire de recherche
  },
  urls: {
    serviceFetch: (agenceDebiteur) =>
      `/Hffintranet/serviceDebiteurPlanning-fetch/${agenceDebiteur}`,
  },
};

// Sélection des éléments du DOM
const agenceDebiteurInput = document.querySelector(
  config.elements.agenceDebiteurInput
);
const serviceDebiteurInput = document.querySelector(
  config.elements.serviceDebiteurInput
);
const searchForm = document.querySelector(config.elements.searchForm);

// Initialisation des checkbox au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  ensureSelectAllCheckbox();
  attachCheckboxEventListeners();
  selectAllCheckboxByDefault();

  // Ajout d'un écouteur pour recalculer après la soumission du formulaire
  searchForm.addEventListener("submit", () => {
    setTimeout(() => {
      ensureSelectAllCheckbox();
      attachCheckboxEventListeners();
      selectAllCheckboxByDefault(); // Recalcule l'état après l'envoi
    }, 100);
  });
});

// Gestionnaire principal pour le changement de l'agence
agenceDebiteurInput.addEventListener("change", handleAgenceChange);

function handleAgenceChange() {
  serviceDebiteurInput.disabled = false;
  // Récupération de l'agence sélectionnée
  const agenceDebiteur =
    agenceDebiteurInput.value === "" ? null : agenceDebiteurInput.value;

  clearServiceCheckboxes();
  removeSelectAllCheckbox();

  if (!agenceDebiteur) {
    // Si aucune agence n'est sélectionnée, on arrête ici
    return;
  }

  // URL pour fetch
  const url = config.urls.serviceFetch(agenceDebiteur);

  // Création et affichage du spinner
  const spinner = createSpinner();
  serviceDebiteurInput.parentElement.appendChild(spinner);

  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      updateServiceCheckboxes(services);
      ensureSelectAllCheckbox();
      attachCheckboxEventListeners();
      selectAllCheckboxByDefault(); // Ensure default selection after updating checkboxes
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => {
      // Suppression du spinner
      spinner.remove();
    });
}

// Fonction pour retirer le bouton "Tout sélectionner"
function removeSelectAllCheckbox() {
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );
  if (selectAllCheckbox) {
    selectAllCheckbox.parentElement.remove();
  }
}

/// Fonction pour créer le spinner HTML avec CSS intégré
function createSpinner() {
  // Conteneur du spinner
  const spinnerContainer = document.createElement("div");
  spinnerContainer.id = "serviceSpinner";
  spinnerContainer.style.display = "flex";
  spinnerContainer.style.justifyContent = "center";
  spinnerContainer.style.alignItems = "center";
  spinnerContainer.style.margin = "20px 0";

  // Spinner
  const spinner = document.createElement("div");
  spinner.className = "spinner-border";
  spinner.role = "status";
  spinner.style.width = "3rem";
  spinner.style.height = "3rem";
  spinner.style.border = "0.25em solid #ccc";
  spinner.style.borderTop = "0.25em solid #000";
  spinner.style.borderRadius = "50%";
  spinner.style.animation = "spin 0.8s linear infinite";

  // Texte pour les lecteurs d'écran (optionnel)
  const spinnerText = document.createElement("span");
  spinnerText.className = "sr-only";
  spinnerText.textContent = "Chargement...";

  spinner.appendChild(spinnerText);
  spinnerContainer.appendChild(spinner);

  // Ajout des styles d'animation au document (si nécessaire)
  const style = document.createElement("style");
  style.textContent = `
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  `;
  document.head.appendChild(style);

  return spinnerContainer;
}

function updateServiceCheckboxes(services) {
  clearServiceCheckboxes();
  addServiceCheckboxes(services);
}

function clearServiceCheckboxes() {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => checkbox.parentElement.remove());
}

function ensureSelectAllCheckbox() {
  let selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );

  if (!selectAllCheckbox) {
    const selectAllDiv = document.createElement("div");
    selectAllDiv.className = "form-check";

    selectAllCheckbox = document.createElement("input");
    selectAllCheckbox.type = "checkbox";
    selectAllCheckbox.id = "planning_search_selectAll";
    selectAllCheckbox.className = "form-check-input";

    const selectAllLabel = document.createElement("label");
    selectAllLabel.htmlFor = selectAllCheckbox.id;
    selectAllLabel.textContent = "Tout sélectionner";
    selectAllLabel.className = "form-check-label";

    selectAllDiv.appendChild(selectAllCheckbox);
    selectAllDiv.appendChild(selectAllLabel);
    serviceDebiteurInput.insertBefore(
      selectAllDiv,
      serviceDebiteurInput.firstChild
    );

    selectAllCheckbox.addEventListener("change", handleSelectAllChange);
  }
}

function handleSelectAllChange(event) {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => {
    checkbox.checked = event.target.checked;
  });
}

function addServiceCheckboxes(services) {
  services.forEach((service, index) => {
    const div = document.createElement("div");
    div.className = "form-check";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.name = "planning_search[serviceDebite][]";
    checkbox.value = service.value;
    checkbox.id = `service_${index}`;
    checkbox.className = "form-check-input";
    checkbox.checked = true; // Set all checkboxes to checked by default

    const label = document.createElement("label");
    label.htmlFor = checkbox.id;
    label.textContent = service.text;
    label.className = "form-check-label";

    div.appendChild(checkbox);
    div.appendChild(label);
    serviceDebiteurInput.appendChild(div);
  });
}

function attachCheckboxEventListeners() {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => {
    checkbox.removeEventListener("change", handleServiceCheckboxChange);
    checkbox.addEventListener("change", handleServiceCheckboxChange);
  });
}

function handleServiceCheckboxChange() {
  const allCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );

  const allChecked = Array.from(allCheckboxes).every(
    (checkbox) => checkbox.checked
  );

  selectAllCheckbox.checked = allChecked;
}

function selectAllCheckboxByDefault() {
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );

  if (serviceCheckboxes.length > 0) {
    const allChecked = Array.from(serviceCheckboxes).every(
      (checkbox) => checkbox.checked
    );

    selectAllCheckbox.checked = allChecked;
  } else {
    selectAllCheckbox.checked = false;
  }
}

/** *======================
 * LIST DETAIL MODAL
 *  =======================*/

document.addEventListener("DOMContentLoaded", (event) => {
  let abortController; // AbortController pour annuler les requêtes fetch précédentes

  const listeCommandeModal = document.getElementById("listeCommande");

  // Gestionnaire pour l'ouverture du modal
  listeCommandeModal.addEventListener("show.bs.modal", function (event) {
    // Annuler les requêtes fetch en cours s'il y en a
    if (abortController) {
      abortController.abort();
    }

    abortController = new AbortController(); // Créer un nouveau contrôleur

    const button = event.relatedTarget; // Bouton qui a déclenché le modal
    const orIntv = button.getAttribute("data-id");
    const numDit = button.getAttribute("data-numDit");
    const migration = button.getAttribute("data-migration");
    const dossierDitLink = document.getElementById("dossierDitLink");
    if (migration == "1") {
      dossierDitLink.style.display = "none";
    }

    dossierDitLink.onclick = (event) => {
      event.preventDefault();
      window.open(
        `/Hffintranet/dw-intervention-atelier-avec-dit/${numDit}`,
        "_blank"
      );
    };

    // Afficher le spinner
    document.getElementById("loading").style.display = "block";
    document.getElementById("dataContent").style.display = "none";

    const numOr = orIntv.split("-")[0];
    const numItv = orIntv.split("-")[1];

    // Utiliser AbortController pour fetchDetailModal
    fetchDetailModal(orIntv, abortController.signal);
    fetchTechnicienInterv(numOr, numItv, abortController.signal);
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    const tableBody = document.getElementById("commandesTableBody");
    const tableBodyOR = document.getElementById("commandesTableBodyOR");
    const tableBodyLign = document.getElementById("commandesTableBodyLign");
    const Ornum = document.getElementById("orIntv");
    const planningTableHead = document.getElementById("planningTableHead");

    tableBody.innerHTML = ""; // Vider le tableau
    tableBodyLign.innerHTML = "";
    tableBodyOR.innerHTML = "";
    Ornum.innerHTML = "";
    planningTableHead.innerHTML = "";
  });

  function masquerSpinner() {
    // Masquer le spinner et afficher les données
    document.getElementById("loading").style.display = "none";
    document.getElementById("dataContent").style.display = "block";
  }

  function fetchTechnicienInterv(numOr, numItv, signal) {
    fetch(`/Hffintranet/api/technicien-intervenant/${numOr}/${numItv}`, {
      signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        const tableBody = document.getElementById("technicienTableBody");

        tableBody.innerHTML = ""; // Clear previous data

        if (data.length > 0) {
          data.forEach((technicien) => {
            let nomPrenom = technicien.matriculenomprenom.split("-")[1];
            // Affichage
            let row = `<tr>
              <td>${technicien.matricule}</td> 
              <td>${nomPrenom}</td> 
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
        if (error.name === "AbortError") {
          console.log("Requête annulée !");
        } else {
          const tableBody = document.getElementById("technicienTableBody");
          tableBody.innerHTML =
            '<tr><td colspan="5">Could not retrieve data.</td></tr>';
          console.error("There was a problem with the fetch operation:", error);
        }
      });
  }

  function fetchDetailModal(id, signal) {
    // Fetch request to get the data
    fetch(`/Hffintranet/detail-modal/${id}`, { signal })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        console.log(data.avecOnglet);

        displayOnglet(data.avecOnglet);
        const Ornum = document.getElementById("orIntv");
        const tableBody = document.getElementById("commandesTableBody");
        const planningTableHead = document.getElementById("planningTableHead");
        const tableBodyOR = document.getElementById("commandesTableBodyOR");
        const planningTableHeadOR = document.getElementById(
          "planningTableHeadOR"
        );
        const tableBodyLign = document.getElementById("commandesTableBodyLign");
        const planningTableHeadLign = document.getElementById(
          "planningTableHeadLign"
        );

        tableBody.innerHTML = ""; // Clear previous data
        Ornum.innerHTML = "";
        planningTableHead.innerHTML = "";
        planningTableHeadOR.innerHTML = "";
        planningTableHeadLign.innerHTML = "";

        if (data.data.length > 0) {
          if (data.data[0].numor.startsWith("5")) {
            let rowHeader = `<th>N° OR</th>
                            <th>Intv</th>
                            <th>N° CIS</th>
                            <th>N° Commande</th>
                            <th>Statut ctrmrq</th>
                            <th>CST</th>
                            <th>Ref</th>
                            <th>Désignation</th>
                            <th>Qté OR</th>
                            <th>Qté ALL</th>
                            <th>QTé RLQ</th>
                            <th>QTé LIV</th>
                            <th>Statut</th>
                            <th>Date Statut</th>
                            <th>ETA Ivato</th>
                            <th>ETA Magasin</th>
                            <th>Message</th>`;
            planningTableHead.innerHTML += rowHeader;
            planningTableHeadOR.innerHTML += rowHeader;
            planningTableHeadLign.innerHTML += rowHeader;
          } else {
            let rowHeader = `<th>N° OR</th>
                            <th>Intv</th>
                            <th>N° Commande</th>
                            <th>Statut ctrmrq</th>
                            <th>CST</th>
                            <th>Ref</th>
                            <th>Désignation</th>
                            <th>Qté OR</th>
                            <th>Qté ALL</th>
                            <th>QTé RLQ</th>
                            <th>QTé LIV</th>
                            <th>Statut</th>
                            <th>Date Statut</th>
                            <th>ETA Ivato</th>
                            <th>ETA Magasin</th>
                            <th>Message</th>`;
            planningTableHead.innerHTML += rowHeader;
          }
          data.data.forEach((detail) => {
            console.log(detail);

            Ornum.innerHTML = `${detail.numor} - ${detail.intv} | intitulé : ${detail.commentaire} | `;
            if (detail.plan == "PLANIFIE") {
              Ornum.innerHTML += `planifié le : ${formaterDate(
                detail.dateplanning
              )}`;
            } else {
              Ornum.innerHTML += `date début : ${formaterDate(
                detail.dateplanning
              )}`;
            }
            // Formater la date
            let dateEtaIvato;
            let dateMagasin;
            let dateStatut;
            let numCis;
            let numCde;
            let numeroCdeCis;
            let statrmq;
            let StatutCtrmqCis;
            let statut;
            let message;
            let cmdColorRmq = "";
            let numRef;
            if (
              formaterDate(detail.datestatut) == "01/01/1970" ||
              formaterDate(detail.datestatut) == "01/01/1900"
            ) {
              dateStatut = "";
            } else {
              dateStatut = formaterDate(detail.datestatut);
            }
            if (
              detail.Eta_ivato == "" ||
              formaterDate(detail.Eta_ivato) === "01/01/1900"
            ) {
              dateEtaIvato = "";
            } else {
              dateEtaIvato = formaterDate(detail.Eta_ivato);
            }
            if (
              detail.Eta_magasin == "" ||
              formaterDate(detail.Eta_magasin) === "01/01/1900"
            ) {
              dateMagasin = "";
            } else {
              dateMagasin = formaterDate(detail.Eta_magasin);
            }
            if (detail.numerocmd == null) {
              numCde = "";
            } else {
              numCde = detail.numerocmd;
            }
            if (detail.ref == null) {
              numRef = "";
            } else {
              numRef = detail.ref;
            }
            if (detail.statut_ctrmq == null) {
              statrmq = "";
            } else {
              statrmq = detail.statut_ctrmq;
            }
            if (detail.statut == null) {
              statut = "";
            } else {
              statut = detail.statut;
            }
            if (detail.message == null) {
              message = "";
            } else {
              message = detail.message;
            }

            if (detail.numcis == "0") {
              numCis = "";
            } else {
              numCis = detail.numcis;
            }
            if (detail.numerocdecis == null) {
              numeroCdeCis = "";
            } else {
              numeroCdeCis = detail.numerocdecis;
            }
            if (detail.statut_ctrmq_cis == null) {
              StatutCtrmqCis = "";
            } else {
              StatutCtrmqCis = detail.statut_ctrmq_cis;
            }

            //reception partiel
            let qteSolde = parseInt(detail.qteSlode);
            let qteQte = parseInt(detail.qte);

            if (qteSolde > 0 && qteSolde != qteQte) {
              cmdColorRmq = 'style="background-color: yellow;"';
            }
            let cmdColor;
            let Ord = detail.Ord;
            if (statut == "DISPO STOCK") {
              cmdColor = 'style="background-color: #c8ad7f; color: white;"';
            } else if (statut == "Error" || statut == "Back Order") {
              cmdColor = 'style="background-color: red; color: white;"';
            } else if (Ord == "ORD") {
              cmdColor = 'style="background-color:#9ACD32  ; color: white;"';
            }
            //onglet CIS
            let statutCIS;
            let dateStatutCIS;

            if (
              parseInt(detail.qtelivlig) > 0 &&
              parseInt(detail.qtealllig) === 0 &&
              parseInt(detail.qterlqlig) === 0
            ) {
              statutCIS = "LIVRE";
              dateStatutCIS = formaterDate(detail.dateLivLIg);
            } else if (parseInt(detail.qtealllig) > 0) {
              statutCIS = "A LIVRER";
              dateStatutCIS = formaterDate(detail.dateAllLIg);
            } else {
              statutCIS = detail.statut;
              dateStatutCIS = "";
            }

            if (detail.numor && detail.numor.startsWith("5")) {
              // Affichage
              let row = `<tr>
                        <td>${detail.numor}</td> 
                        <td>${detail.intv}</td> 
                        <td>${numCis}</td> 
                        <td ></td> 
                        <td ></td> 
                        <td>${detail.cst}</td> 
                        <td>${numRef}</td> 
                        <td>${detail.desi}</td> 
                        <td>${parseInt(detail.qteres_or)}</td> 
                        <td>${parseInt(detail.qteall)}</td> 
                        <td>${parseInt(detail.qtereliquat)}</td> 
                        <td>${parseInt(detail.qteliv)}</td> 
                         <td  ${cmdColor}>${statut} </td> 
                        <td>${dateStatut}</td> 
                        <td></td> 
                        <td></td> 
                        <td></td> 
                    </tr>`;
              // tableBody.innerHTML += row;
              tableBodyOR.innerHTML += row;
              if (numCis) {
                let row1 = `<tr>
                        <td>${detail.numor}</td> 
                        <td>${detail.intv}</td> 
                        <td>${numCis}</td> 
                        <td ${cmdColor}>${numeroCdeCis}</td> 
                        <td ${cmdColorRmq}>${StatutCtrmqCis}</td> 
                        <td>${detail.cst}</td> 
                        <td>${numRef}</td> 
                        <td>${detail.desi}</td> 
                        <td>${
                          isNaN(detail.qteORlig) || detail.qteORlig === ""
                            ? ""
                            : parseInt(detail.qteORlig)
                        }</td> 
                        <td>${
                          isNaN(detail.qtealllig) || detail.qtealllig === ""
                            ? ""
                            : parseInt(detail.qtealllig)
                        }</td> 
                        <td>${
                          isNaN(detail.qterlqlig) || detail.qterlqlig === ""
                            ? ""
                            : parseInt(detail.qterlqlig)
                        }</td> 
                        <td>${
                          isNaN(detail.qtelivlig) || detail.qtelivlig === ""
                            ? ""
                            : parseInt(detail.qtelivlig)
                        }</td> 
                        <td >${statutCIS === null ? "" : statutCIS}</td> 
                        <td>${dateStatutCIS === null ? "" : dateStatutCIS}</td> 
                        <td>${dateEtaIvato}</td> 
                        <td>${dateMagasin}</td> 
                        <td>${message}</td> 
                    </tr>`;
                tableBodyLign.innerHTML += row1;
              }
            } else {
              // Affichage
              let row = `<tr>
                      <td>${detail.numor}</td> 
                      <td>${detail.intv}</td> 
                      <td ${cmdColor}>${numCde}</td> 
                      <td ${cmdColorRmq}>${statrmq}</td> 
                      <td>${detail.cst}</td> 
                      <td>${numRef}</td> 
                      <td>${detail.desi}</td> 
                      <td>${parseInt(detail.qteres_or)}</td> 
                      <td>${parseInt(detail.qteall)}</td> 
                      <td>${parseInt(detail.qtereliquat)}</td> 
                      <td>${parseInt(detail.qteliv)}</td> 
                      <td >${statut}</td> 
                      <td>${dateStatut}</td> 
                      <td>${dateEtaIvato}</td> 
                      <td>${dateMagasin}</td> 
                      <td>${message}</td> 
                  </tr>`;
              tableBody.innerHTML += row;
            }
          });

          masquerSpinner();
        } else {
          // Si les données sont vides, afficher un message vide
          tableBody.innerHTML =
            '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
          masquerSpinner();
        }
      })
      .catch((error) => {
        if (error.name === "AbortError") {
          console.log("Requête annulée !");
        } else {
          const tableBody = document.getElementById("commandesTableBody");
          tableBody.innerHTML =
            '<tr><td colspan="5">Could not retrieve data.</td></tr>';
          console.error("There was a problem with the fetch operation:", error);
          masquerSpinner();
        }
      });
  }

  function displayOnglet(show) {
    const avecOnglet = document.getElementById("avec_onglet");
    const sansOnglet = document.getElementById("sans_onglet");
    if (show) {
      avecOnglet.classList.remove("d-none");
      sansOnglet.classList.add("d-none");
    } else {
      avecOnglet.classList.add("d-none");
      sansOnglet.classList.remove("d-none");
    }
  }

  function formaterDate(daty) {
    const date = new Date(daty);
    return `${date.getDate().toString().padStart(2, "0")}/${(
      date.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}/${date.getFullYear()}`;
  }

  /**
   * pour le separateur et fusion des numOR
   *
   * */
  const tableBody = document.querySelector("#tableBody");
  const rows = document.querySelectorAll("#tableBody tr");

  let previousOrNumber = null;
  let rowSpanCount = 0;
  let firstRowInGroup = null;

  for (var i = 0; i < rows.length; i++) {
    let currentRow = rows[i];
    let orNumberCell = currentRow.getElementsByTagName("td")[2]; // Modifier l'indice selon la position du numéro OR
    let currentOrNumber = orNumberCell ? orNumberCell.textContent.trim() : null;

    if (previousOrNumber === null) {
      // Initialisation pour la première ligne
      firstRowInGroup = currentRow;
      rowSpanCount = 1;
    } else if (previousOrNumber && previousOrNumber !== currentOrNumber) {
      if (firstRowInGroup) {
        let cellToRowspanNumDit = firstRowInGroup.getElementsByTagName("td")[1]; // Modifier l'indice selon la position du numéro OR
        let cellToRowspanNumOr = firstRowInGroup.getElementsByTagName("td")[2];
        let cellToRowspanInter = firstRowInGroup.getElementsByTagName("td")[7];
        let cellToRowspanAgence = firstRowInGroup.getElementsByTagName("td")[5];
        let cellToRowspanService =
          firstRowInGroup.getElementsByTagName("td")[6];
        cellToRowspanNumDit.rowSpan = rowSpanCount;
        cellToRowspanNumOr.rowSpan = rowSpanCount;
        cellToRowspanInter.rowSpan = rowSpanCount;
        cellToRowspanAgence.rowSpan = rowSpanCount;
        cellToRowspanService.rowSpan = rowSpanCount;
        cellToRowspanNumDit.classList.add("rowspan-cell");
        cellToRowspanNumOr.classList.add("rowspan-cell");
        cellToRowspanInter.classList.add("rowspan-cell");
        cellToRowspanAgence.classList.add("rowspan-cell");
        cellToRowspanService.classList.add("rowspan-cell");
      }

      // Début pour le séparateur
      let separatorRow = document.createElement("tr");
      separatorRow.classList.add("separator-row");
      let td = document.createElement("td");
      td.colSpan = currentRow.cells.length;
      td.classList.add("p-0");
      separatorRow.appendChild(td);
      tableBody.insertBefore(separatorRow, currentRow);
      // Fin pour le séparateur

      rowSpanCount = 1;
      firstRowInGroup = currentRow;
    } else {
      rowSpanCount++;
      if (firstRowInGroup !== currentRow) {
        currentRow.getElementsByTagName("td")[2].style.display = "none";
        currentRow.getElementsByTagName("td")[1].style.display = "none";
        currentRow.getElementsByTagName("td")[7].style.display = "none";
        currentRow.getElementsByTagName("td")[5].style.display = "none";
        currentRow.getElementsByTagName("td")[6].style.display = "none";
      }
    }

    previousOrNumber = currentOrNumber;
  }

  // Appliquer le rowspan à la dernière série de lignes
  if (firstRowInGroup) {
    let cellToRowspanNumDit = firstRowInGroup.getElementsByTagName("td")[1]; // Modifier l'indice selon la position du numéro OR
    let cellToRowspanNumOr = firstRowInGroup.getElementsByTagName("td")[2];
    let cellToRowspanInter = firstRowInGroup.getElementsByTagName("td")[7];
    let cellToRowspanAgence = firstRowInGroup.getElementsByTagName("td")[5];
    let cellToRowspanService = firstRowInGroup.getElementsByTagName("td")[6];
    cellToRowspanNumDit.rowSpan = rowSpanCount;
    cellToRowspanNumOr.rowSpan = rowSpanCount;
    cellToRowspanInter.rowSpan = rowSpanCount;
    cellToRowspanAgence.rowSpan = rowSpanCount;
    cellToRowspanService.rowSpan = rowSpanCount;
    cellToRowspanNumDit.classList.add("rowspan-cell");
    cellToRowspanNumOr.classList.add("rowspan-cell");
    cellToRowspanInter.classList.add("rowspan-cell");
    cellToRowspanAgence.classList.add("rowspan-cell");
    cellToRowspanService.classList.add("rowspan-cell");
  }
});
