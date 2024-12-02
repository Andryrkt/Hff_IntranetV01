// Fonction : Gestion de l'affichage du modal
export function handleShowCisModal(event, dossierDitLink) {
  const button = event.relatedTarget; // Bouton qui a déclenché le modal
  const orIntv = button.getAttribute("data-id"); // ID extrait
  const numDit = button.getAttribute("data-numDit");
  const migration = button.getAttribute("data-migration");

  console.log(`Migration: ${migration}`);

  // Mettre à jour le lien dynamique
  updateDossierDitLink(dossierDitLink, numDit, migration);

  // Activer le spinner
  toggleSpinner("loadingcis", "dataContentcis", true);

  // Charger les données du modal
  fetchDetailModalCis(orIntv);

  // Charger les techniciens associés
  const [numOr, numItv] = orIntv.split("-");
  fetchTechnicienInterv(numOr, numItv);
}

export function handleShowModal(event, dossierDitLink) {
  const button = event.relatedTarget; // Bouton qui a déclenché le modal
  const orIntv = button.getAttribute("data-id"); // ID extrait
  const numDit = button.getAttribute("data-numDit");
  const migration = button.getAttribute("data-migration");

  console.log(`Migration: ${migration}`);

  // Mettre à jour le lien dynamique
  updateDossierDitLink(dossierDitLink, numDit, migration);

  // Activer le spinner
  toggleSpinner("loadingcis", "dataContentcis", true);

  // Charger les données du modal
  fetchDetailModal(orIntv);

  // Charger les techniciens associés
  const [numOr, numItv] = orIntv.split("-");
  fetchTechnicienInterv(numOr, numItv);
}

// Fonction : Réinitialiser les tableaux
export function resetTables(tableElements) {
  tableElements.forEach((table) => {
    table.innerHTML = ""; // Vider le contenu de chaque table
  });
}
// Fonction : Mise à jour du lien "Dossier Dit"
function updateDossierDitLink(linkElement, numDit, migration) {
  if (migration === "1") {
    linkElement.style.display = "none"; // Masquer si migration == 1
  } else {
    linkElement.style.display = ""; // Afficher sinon
    linkElement.onclick = (event) => {
      event.preventDefault();
      window.open(
        `/Hffintranet/dw-intervention-atelier-avec-dit/${numDit}`,
        "_blank"
      );
    };
  }
}

function toggleSpinner(spinnerId, dataId, show) {
  document.getElementById(spinnerId).style.display = show ? "block" : "none";
  document.getElementById(dataId).style.display = show ? "none" : "block";
}

function fetchDetailModalCis(id) {
  const tableBody = document.getElementById("cisTableBody");

  const url = `/Hffintranet/detail-modal/${id}`;
  fetchData(url)
    .then((data) => {
      updateDonnerCis(data, tableBody);
    })
    .catch((error) => {
      handleError(tableBody, "Could not retrieve data.", error, 17);
      toggleSpinner("loadingcis", "dataContentcis", false);
    });
}

function fetchDetailModal(id) {
  const tableBody = document.getElementById("commandesTableBody");

  const url = `/Hffintranet/detail-modal/${id}`;
  // Fetch request to get the data
  fetchData(url)
    .then((data) => {
      updateDonner(data, tableBody);
    })
    .catch((error) => {
      handleError(tableBody, "Could not retrieve data.", error, 17);
      toggleSpinner("loadingcis", "dataContentcis", false);
    });
}
function fetchTechnicienInterv(numOr, numItv) {
  const tableBodytechnicien = document.getElementById("technicienTableBody");

  // Afficher le spinner
  toggleSpinner("loadingcis", "dataContentcis", true);
  const url = `/Hffintranet/api/technicien-intervenant/${numOr}/${numItv}`;
  fetchData(url)
    .then((data) => {
      updateTechnicienTable(data, tableBodytechnicien);
    })
    .catch((error) => {
      handleError(tableBodytechnicien, "Could not retrieve data.", error);
      toggleSpinner("loadingcis", "dataContentcis", false);
    });
}

// Fonction pour formater une date
function formatDate(dateValue) {
  if (!dateValue || dateValue === "01/01/1900" || dateValue === "01/01/1970") {
    return "";
  }
  const date = new Date(dateValue);
  return `${date.getDate().toString().padStart(2, "0")}/${(date.getMonth() + 1)
    .toString()
    .padStart(2, "0")}/${date.getFullYear()}`;
}

// Fonction pour appliquer la couleur en fonction du statut
function getCmdColor(statut, ord) {
  if (statut === "DISPO STOCK") {
    return 'style="background-color: #c8ad7f; color: white;"';
  } else if (statut === "Error" || statut === "Back Order") {
    return 'style="background-color: red; color: white;"';
  } else if (ord === "ORD") {
    return 'style="background-color:#9ACD32; color: white;"';
  }
  return "";
}

// Fonction pour gérer les champs vides ou par défaut
function sanitizeField(value, defaultValue = "") {
  return value == null || value === "0" || value === "" ? defaultValue : value;
}

function donnerCis(detail) {
  const dateStatut = formatDate(detail.datestatut);
  const dateEtaIvato = formatDate(detail.Eta_ivato);
  const dateMagasin = formatDate(detail.Eta_magasin);

  const numCis = sanitizeField(detail.numcis);
  const numeroCdeCis = sanitizeField(detail.numerocdecis);
  const numerocmd = sanitizeField(detail.numerocmd);
  const numRef = sanitizeField(detail.ref);
  const statutCtrmqCis = sanitizeField(detail.statut_ctrmq_cis);
  const statutCtrmq = sanitizeField(detail.statut_ctrmq);
  const statut = sanitizeField(detail.statut);
  const message = sanitizeField(detail.message);

  // Gestion des quantités partielles
  const qteSolde = parseInt(detail.qteSlode || "0", 10);
  const qteQte = parseInt(detail.qte || "0", 10);
  const cmdColorRmq =
    qteSolde > 0 && qteSolde !== qteQte
      ? 'style="background-color: yellow;"'
      : "";

  const cmdColor = getCmdColor(statut, detail.Ord);

  return {
    dateStatut,
    dateEtaIvato,
    dateMagasin,
    numCis,
    numeroCdeCis,
    numerocmd,
    numRef,
    statutCtrmqCis,
    statutCtrmq,
    statut,
    message,
    cmdColor,
    cmdColorRmq,
  };
}

// Gérer la réponse du fetch
function handleFetchResponse(response) {
  if (!response.ok) {
    throw new Error(`Network response was not ok: ${response.status}`);
  }
  return response.json();
}

// Créer une ligne de tableau pour un détail
function createDetailRowcis(detail, detailFormatted) {
  return `<tr>
            <td>${detail.numor}</td>
            <td>${detail.intv}</td>
            <td>${detailFormatted.numCis}</td>
            <td ${detailFormatted.cmdColor}>${detailFormatted.numeroCdeCis}</td>
            <td ${detailFormatted.cmdColorRmq}>${
    detailFormatted.statutCtrmqCis
  }</td>
            <td>${detail.cst}</td>
            <td>${detailFormatted.numRef}</td>
            <td>${detail.desi}</td>
            <td>${parseInt(detail.qteres_or)}</td>
            <td>${parseInt(detail.qteall)}</td>
            <td>${parseInt(detail.qtereliquat)}</td>
            <td>${parseInt(detail.qteliv)}</td>
            <td>${detailFormatted.statut}</td>
            <td>${detailFormatted.dateStatut}</td>
            <td>${detailFormatted.dateEtaIvato}</td>
            <td>${detailFormatted.dateMagasin}</td>
            <td>${detailFormatted.message}</td>
          </tr>`;
}

// Mettre à jour l'en-tête avec numor et intv
function formatOrIntv(detail) {
  return `${detail.numor} - ${detail.intv}`;
}

// Afficher un message si aucune donnée n'est disponible
function displayEmptyMessage(tableBody, message, colspan = 2) {
  tableBody.innerHTML = `<tr><td colspan="${colspan}">${message}</td></tr>`;
}

// Gérer les erreurs
function handleError(tableBody, message, error, colspan = 2) {
  displayEmptyMessage(tableBody, message, colspan);
  console.error("Error:", error);
}

// Créer une ligne de tableau pour un technicien
function createTechnicienRow(technicien) {
  const nomPrenom = technicien.matriculenomprenom.split("-")[1];
  return `<tr>
            <td>${technicien.matricule}</td>
            <td>${nomPrenom}</td>
          </tr>`;
}

async function fetchData(url) {
  try {
    const response = await fetch(url);
    return handleFetchResponse(response);
  } catch (error) {
    console.error("Fetch Error:", error);
    throw error; // Propager l'erreur pour un traitement ultérieur
  }
}

function updateTechnicienTable(data, tableBodytechnicien) {
  tableBodytechnicien.innerHTML = ""; // Vider les données précédentes

  if (data.length > 0) {
    data.forEach((technicien) => {
      const row = createTechnicienRow(technicien);
      tableBodytechnicien.innerHTML += row;
    });
  } else {
    displayEmptyMessage(tableBodytechnicien, "Aucune donnée disponible.");
  }

  //toggleSpinner("loadingcis", "dataContentcis", false);
}

function updateDonnerCis(data, tableBody) {
  const Ornumcis = document.getElementById("orIntvcis");
  tableBody.innerHTML = ""; // Vider les données précédentes

  if (data.length > 0) {
    Ornumcis.innerHTML = formatOrIntv(data[0]); // Mettre à jour le titre avec numor et intv

    data.forEach((detail) => {
      const detailFormatted = donnerCis(detail);
      tableBody.innerHTML += createDetailRowcis(detail, detailFormatted);
    });

    toggleSpinner("loadingcis", "dataContentcis", false);
  } else {
    displayEmptyMessage(tableBody, "Aucune donnée disponible.", 17);
  }

  toggleSpinner("loadingcis", "dataContentcis", false);
}

function updateDonner(data, tableBody) {
  // const tableBody = document.getElementById("commandesTableBody");
  const Ornum = document.getElementById("orIntv");

  tableBody.innerHTML = ""; // Clear previous data

  if (data.length > 0) {
    Ornum.innerHTML = formatOrIntv(data[0]);
    data.forEach((detail) => {
      const detailFormatted = donnerCis(detail);
      tableBody.innerHTML += createDetailRow(detail, detailFormatted);
    });

    toggleSpinner("loadingcis", "dataContentcis", false);
  } else {
    displayEmptyMessage(tableBody, "Aucune donnée disponible.", 17);
  }

  toggleSpinner("loadingcis", "dataContentcis", false);
}

function createDetailRow(detail, detailFormatted) {
  return `<tr>
                <td>${detail.numor}</td> 
                <td>${detail.intv}</td> 
                <td ${detailFormatted.cmdColor}>${
    detailFormatted.numerocmd
  }</td> 
                <td ${detailFormatted.cmdColorRmq}>${
    detailFormatted.statutCtrmq
  }</td> 
                <td>${detail.cst}</td> 
                <td>${detailFormatted.numRef}</td> 
                <td>${detail.desi}</td> 
                <td>${parseInt(detail.qteres_or)}</td> 
                <td>${parseInt(detail.qteall)}</td> 
                <td>${parseInt(detail.qtereliquat)}</td> 
                <td>${parseInt(detail.qteliv)}</td> 
                <td >${detailFormatted.statut}</td> 
                <td>${detailFormatted.dateStatut}</td> 
                <td>${detailFormatted.dateEtaIvato}</td> 
                <td>${detailFormatted.dateMagasin}</td> 
                <td>${detailFormatted.message}</td> 
            </tr>`;
}
