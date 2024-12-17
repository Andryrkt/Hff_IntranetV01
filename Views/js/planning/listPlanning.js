/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
const agenceDebiteurInput = document.querySelector(
  "#planning_search_agenceDebite"
);
const serviceDebiteurInput = document.querySelector(
  "#planning_search_serviceDebite"
);
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  serviceDebiteurInput.disabled = false;

  const agenceDebiteur = agenceDebiteurInput.value;
  let url = `/Hffintranet/serviceDebiteurPlanning-fetch/${agenceDebiteur}`;

  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);

      // Si "Tout sélectionner" n'existe pas, l'ajouter
      let selectAllCheckbox = document.querySelector(
        "#planning_search_selectAll"
      );
      if (!selectAllCheckbox) {
        var selectAllDiv = document.createElement("div");
        selectAllDiv.className = "form-check";

        selectAllCheckbox = document.createElement("input");
        selectAllCheckbox.type = "checkbox";
        selectAllCheckbox.id = "planning_search_selectAll";
        selectAllCheckbox.className = "form-check-input";

        var selectAllLabel = document.createElement("label");
        selectAllLabel.htmlFor = selectAllCheckbox.id;
        selectAllLabel.appendChild(
          document.createTextNode("Tout sélectionner")
        );
        selectAllLabel.className = "form-check-label";

        selectAllDiv.appendChild(selectAllCheckbox);
        selectAllDiv.appendChild(selectAllLabel);

        serviceDebiteurInput.appendChild(selectAllDiv);

        // Ajouter l'événement pour "Tout sélectionner"
        selectAllCheckbox.addEventListener("change", (event) => {
          const serviceCheckboxes = document.querySelectorAll(
            'input[name="planning_search[serviceDebite][]"]'
          );
          serviceCheckboxes.forEach((checkbox) => {
            checkbox.checked = event.target.checked;
          });
        });
      }

      // Effacer uniquement les cases des services (pas "Tout sélectionner")
      const serviceCheckboxes = document.querySelectorAll(
        'input[name="planning_search[serviceDebite][]"]'
      );
      serviceCheckboxes.forEach((checkbox) => checkbox.parentElement.remove());

      // Ajouter les cases des services débiteurs
      for (var i = 0; i < services.length; i++) {
        var div = document.createElement("div");
        div.className = "form-check";

        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "planning_search[serviceDebite][]";
        checkbox.value = services[i].value;
        checkbox.id = "service_" + i;
        checkbox.className = "form-check-input";

        var label = document.createElement("label");
        label.htmlFor = checkbox.id;
        label.appendChild(document.createTextNode(services[i].text));
        label.className = "form-check-label";

        div.appendChild(checkbox);
        div.appendChild(label);

        serviceDebiteurInput.appendChild(div);

        // Ajouter un gestionnaire d'événements pour déselectionner "Tout sélectionner"
        checkbox.addEventListener("change", () => {
          if (!checkbox.checked) {
            selectAllCheckbox.checked = false;
          }

          // Vérifier si toutes les cases sont cochées
          const allChecked = [
            ...document.querySelectorAll(
              'input[name="planning_search[serviceDebite][]"]'
            ),
          ].every((cb) => cb.checked);
          selectAllCheckbox.checked = allChecked;
        });
      }
    })
    .catch((error) => console.error("Error:", error));
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
    const Ornum = document.getElementById("orIntv");
    const planningTableHead = document.getElementById("planningTableHead");

    tableBody.innerHTML = ""; // Vider le tableau
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
        console.log(data);

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
        const tableBody = document.getElementById("commandesTableBody");
        const Ornum = document.getElementById("orIntv");
        const planningTableHead = document.getElementById("planningTableHead");

        tableBody.innerHTML = ""; // Clear previous data
        Ornum.innerHTML = "";
        planningTableHead.innerHTML = "";

        if (data.length > 0) {
          if (data[0].numor.startsWith("5")) {
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
          data.forEach((detail) => {
            console.log(detail);

            Ornum.innerHTML = `${detail.numor} - ${detail.intv} | ${
              detail.commentaire
            } | ${formaterDate(detail.dateplanning)}`;

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
            if (detail.numor && detail.numor.startsWith("5")) {
              // Affichage
              let row = `<tr>
                        <td>${detail.numor}</td> 
                        <td>${detail.intv}</td> 
                        <td>${numCis}</td> 
                        <td ${cmdColor}>${numeroCdeCis}</td> 
                        <td ${cmdColorRmq}>${StatutCtrmqCis}</td> 
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
