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
  const agenceDebiteur = agenceDebiteurInput.value;
  const url = config.urls.serviceFetch(agenceDebiteur);

  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      updateServiceCheckboxes(services);
      ensureSelectAllCheckbox();
      attachCheckboxEventListeners();
      selectAllCheckboxByDefault(); // Ensure default selection after updating checkboxes
    })
    .catch((error) => console.error("Error:", error));
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

    // Initialiser un nouveau AbortController
    abortController = new AbortController();

    // Récupérer les attributs du bouton déclencheur
    const button = event.relatedTarget; // Bouton qui a déclenché le modal
    const orIntv = button.getAttribute("data-id");
    const numDit = button.getAttribute("data-numDit");
    const migration = button.getAttribute("data-migration");

    const dossierDitLink = document.getElementById("dossierDitLink");
    const loading = document.getElementById("loading");
    const dataContent = document.getElementById("dataContent");

    // Gérer l'affichage du lien selon la migration
    dossierDitLink.style.display = migration === "1" ? "none" : "block";

    // Ajouter un gestionnaire d'événement pour le lien
    dossierDitLink.onclick = (event) => {
      event.preventDefault();
      window.open(
        `/Hffintranet/dw-intervention-atelier-avec-dit/${numDit}`,
        "_blank"
      );
    };

    // Afficher le spinner
    toggleSpinner(loading, dataContent, true);

    // Extraire numOr et numItv de orIntv
    const [numOr, numItv] = orIntv.split("-");

    // Utiliser AbortController pour fetchDetailModal
    fetchDetailModal(orIntv, abortController.signal, loading, dataContent);
    fetchTechnicienInterv(numOr, numItv, abortController.signal);
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    clearTableContents();
  });

  function masquerSpinner() {
    // Masquer le spinner et afficher les données
    document.getElementById("loading").style.display = "none";
    document.getElementById("dataContent").style.display = "block";
  }

  function toggleSpinner(spinnerService, serviceContainer, show) {
    spinnerService.style.display = show ? "block" : "none";
    serviceContainer.style.display = show ? "none" : "block";
  }

  function fetchTechnicienInterv(numOr, numItv, signal) {
    const tableContainer = document.querySelector("#table-container");
    const url = `/Hffintranet/api/technicien-intervenant/${numOr}/${numItv}`;
    fetch(url, { signal })
      .then(handleFetchResponse)
      .then((data) => {
        affichageDataTechnicienDansUnTableau(data);
      })
      .catch((error) => {
        if (error.name === "AbortError") {
          console.log("Requête annulée !");
        } else {
          tableContainer.innerHTML = "Could not retrieve data";
          console.error("There was a problem with the fetch operation:", error);
        }
      });
  }

  /**
   * fonction n qui permet d'afficher le matricule et le nom et prenom du technicient dan sun tableau
   * @param {array} data
   */
  function affichageDataTechnicienDansUnTableau(data) {
    // Colonnes à afficher dans le tableau
    const columns = [
      { key: "matricule", label: "Matricule", align: "center" },
      { key: "nomPrenom", label: "Nom et prenom(s)" },
    ];

    // Effacer le contenu précédent
    document.getElementById("table-container").innerHTML = "";
    // Initialiser le tableau
    const tableau = new TableauComponent({
      columns: columns,
      data: data,
      theadClass: "table", // Optionnel : classe personnalisée pour l'en-tête
    });

    // Monter le tableau dans le conteneur
    tableau.mount("table-container");
  }

  // function ancienDataTechnicient() {
  //   const tableBody = document.getElementById("technicienTableBody");

  //   tableBody.innerHTML = ""; // Clear previous data

  //   if (data.length > 0) {
  //     data.forEach((technicien) => {
  //       let nomPrenom = technicien.matriculenomprenom.split("-")[1];
  //       // Affichage
  //       let row = `<tr>
  //             <td>${technicien.matricule}</td>
  //             <td>${nomPrenom}</td>
  //         </tr>`;
  //       tableBody.innerHTML += row;
  //     });
  //   } else {
  //     // Si les données sont vides, afficher un message vide
  //     tableBody.innerHTML =
  //       '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
  //   }
  // }
  function fetchDetailModal(id, signal, loading, dataContent) {
    const url = `/Hffintranet/detail-modal/${id}`;
    // Fetch request to get the data
    fetch(url, { signal })
      .then(handleFetchResponse)
      .then((data) => {
        if (data.length > 0) {
          const isTypeCis = data[0].numor.startsWith("5");
          console.log("isTypeCis:", isTypeCis);

          data.forEach((detail) => {
            updateOrDetails(detail);
          });

          // Définir les colonnes dynamiquement
          const columns = [
            { key: "numor", label: "N° OR", align: "center" },
            { key: "intv", label: "Intv", align: "left" },
            ...(isTypeCis
              ? [{ key: "numcis", label: "N° CIS", align: "center" }]
              : []),
            {
              key: "numCde",
              label: "N° Commande",
              styles: (row) => {
                const style = getCmdColor(row);
                console.log(`Style for numCde (${row.numCde}):`, style);
                return style;
              },
              align: "center",
            },
            {
              key: "statrmq",
              label: "Statut ctrmrq",
              styles: (row) => {
                const style = getCmdColorRmq(row);
                console.log(`Style for statrmq (${row.statrmq}):`, style);
                return style;
              },
              align: "center",
            },
            { key: "cst", label: "CST", align: "center" },
            { key: "ref", label: "Ref", align: "left" },
            { key: "qteres_or", label: "Qté OR", align: "center" },
            { key: "qteall", label: "Qté ALL", align: "center" },
            { key: "qtereliquat", label: "Qté RLQ", align: "center" },
            { key: "qteliv", label: "Qté LIV", align: "center" },
            { key: "statut", label: "Statut", align: "center" },
            { key: "datestatut", label: "Date Statut", align: "center" },
            { key: "Eta_ivato", label: "ETA Ivato", align: "center" },
            { key: "Eta_magasin", label: "ETA Magasin", align: "center" },
            { key: "message", label: "Message", align: "left" },
          ];

          console.log("Columns defined:", columns);

          // Formater les données
          const formattedData = data.map((detail) => ({
            numcis: detail.numcis || "",
            numor: detail.numor,
            intv: detail.intv,
            numCde: detail.numerocmd,
            statrmq: isTypeCis
              ? valueOrEmpty(detail.statut_ctrmq_cis)
              : valueOrEmpty(detail.statut_ctrmq),
            cst: detail.cst,
            ref: detail.ref,
            qteres_or: parseInt(detail.qteres_or),
            qteall: parseInt(detail.qteall),
            qtereliquat: parseInt(detail.qtereliquat),
            qteliv: parseInt(detail.qteliv),
            statut: detail.statut,
            datestatut: formatDateOrEmpty(detail.datestatut),
            Eta_ivato: formatDateOrEmpty(detail.Eta_ivato),
            Eta_magasin: formatDateOrEmpty(detail.Eta_magasin),
            message: detail.message,
            Ord: detail.Ord, // Inclure Ord pour les styles dynamiques
            qteSolde: parseInt(detail.qteSolde), // Inclure qteSolde
            qteQte: parseInt(detail.qteQte), // Inclure qteQte
          }));

          // Effacer le contenu précédent
          document.getElementById("table-container-detail").innerHTML = "";

          // Initialiser ou mettre à jour le tableau
          const tableau = new TableauComponent({
            columns: columns,
            data: formattedData,
            theadClass: "table",
          });
          tableau.mount("table-container-detail");

          toggleSpinner(loading, dataContent, false);
        } else {
          displayEmptyMessage();
          toggleSpinner(loading, dataContent, false);
        }
      })
      .catch(handleFetchError);
  }

  function displayEmptyMessage() {
    tableau.props.data = [
      { numor: "Aucune donnée disponible.", intv: "", numCde: "" },
    ];
    tableau.render();
  }

  // function fetchDetailModal(id, signal, loading, dataContent) {
  //   const url = `/Hffintranet/detail-modal/${id}`;
  //   // Fetch request to get the data
  //   fetch(url, { signal })
  //     .then(handleFetchResponse)
  //     .then((data) => {
  //       clearTableContents();
  //       if (data.length > 0) {
  //         const isTypeCis = data[0].numor.startsWith("5");
  //         updateTableHeader(isTypeCis);

  //         data.forEach((detail) => {
  //           updateOrDetails(detail);
  //           const formattedDetail = formatDetail(detail);
  //           const isTypeCis = detail.numor && detail.numor.startsWith("5");
  //           document.getElementById("commandesTableBody").innerHTML +=
  //             createRow(detail, formattedDetail, isTypeCis);
  //         });

  //         toggleSpinner(loading, dataContent, false);
  //       } else {
  //         displayEmptyMessage();
  //         toggleSpinner(loading, dataContent, false);
  //       }
  //     })
  //     .catch(handleFetchError);
  // }

  function handleFetchResponse(response) {
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    return response.json();
  }

  function clearTableContents() {
    // document.getElementById("commandesTableBody").innerHTML = "";
    document.getElementById("orIntv").innerHTML = "";
    // document.getElementById("planningTableHead").innerHTML = "";
  }

  // function updateTableHeader(isTypeCis) {
  //   const planningTableHead = document.getElementById("planningTableHead");
  //   planningTableHead.innerHTML += generateRowHeader(isTypeCis);
  // }

  function updateOrDetails(detail) {
    const Ornum = document.getElementById("orIntv");
    Ornum.innerHTML = `${detail.numor} - ${detail.intv} | intitulé : ${detail.commentaire} | `;
    if (detail.plan === "PLANIFIE") {
      Ornum.innerHTML += `planifié le : ${formaterDate(detail.dateplanning)}`;
    } else {
      Ornum.innerHTML += `date début : ${formaterDate(detail.dateplanning)}`;
    }
  }

  // function formatDetail(detail) {
  //   return {
  //     dateStatut: formatDateOrEmpty(detail.datestatut),
  //     dateEtaIvato: formatDateOrEmpty(detail.Eta_ivato),
  //     dateMagasin: formatDateOrEmpty(detail.Eta_magasin),
  //     numCde: valueOrEmpty(detail.numerocmd),
  //     numRef: valueOrEmpty(detail.ref),
  //     statrmq: valueOrEmpty(detail.statut_ctrmq),
  //     statut: valueOrEmpty(detail.statut),
  //     message: valueOrEmpty(detail.message),
  //     numCis: valueOrEmpty(detail.numcis),
  //     numeroCdeCis: valueOrEmpty(detail.numerocdecis),
  //     StatutCtrmqCis: valueOrEmpty(detail.statut_ctrmq_cis),
  //     cmdColorRmq: getCmdColorRmq(detail),
  //     cmdColor: getCmdColor(detail),
  //   };
  // }

  // function displayEmptyMessage() {
  //   const tableBody = document.getElementById("commandesTableBody");
  //   tableBody.innerHTML =
  //     '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
  // }

  function handleFetchError(error) {
    if (error.name === "AbortError") {
      console.log("Requête annulée !");
    } else {
      const tableBody = document.getElementById("commandesTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="5">Could not retrieve data.</td></tr>';
      console.error("There was a problem with the fetch operation:", error);
      masquerSpinner();
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

  // function generateRowHeader(includeCIS = false) {
  //   let commonHeaders = `
  //       <th>N° OR</th>
  //       <th>Intv</th>
  //       <th>N° Commande</th>
  //       <th>Statut ctrmrq</th>
  //       <th>CST</th>
  //       <th>Ref</th>
  //       <th>Désignation</th>
  //       <th>Qté OR</th>
  //       <th>Qté ALL</th>
  //       <th>QTé RLQ</th>
  //       <th>QTé LIV</th>
  //       <th>Statut</th>
  //       <th>Date Statut</th>
  //       <th>ETA Ivato</th>
  //       <th>ETA Magasin</th>
  //       <th>Message</th>
  //   `;

  //   let cisHeader = `<th>N° CIS</th>`;

  //   return includeCIS ? cisHeader + commonHeaders : commonHeaders;
  // }

  // function createRow(detail, formattedDetail, useCis) {
  //   return `<tr>
  //               <td>${detail.numor}</td>
  //               <td>${detail.intv}</td>
  //               ${useCis ? `<td>${formattedDetail.numCis}</td>` : ""}
  //               <td ${formattedDetail.cmdColor}>${
  //     useCis ? formattedDetail.numeroCdeCis : formattedDetail.numCde
  //   }</td>
  //               <td ${formattedDetail.cmdColorRmq}>${
  //     useCis ? formattedDetail.StatutCtrmqCis : formattedDetail.statrmq
  //   }</td>
  //               <td>${detail.cst}</td>
  //               <td>${formattedDetail.numRef}</td>
  //               <td>${detail.desi}</td>
  //               <td>${parseInt(detail.qteres_or)}</td>
  //               <td>${parseInt(detail.qteall)}</td>
  //               <td>${parseInt(detail.qtereliquat)}</td>
  //               <td>${parseInt(detail.qteliv)}</td>
  //               <td>${formattedDetail.statut}</td>
  //               <td>${formattedDetail.dateStatut}</td>
  //               <td>${formattedDetail.dateEtaIvato}</td>
  //               <td>${formattedDetail.dateMagasin}</td>
  //               <td>${formattedDetail.message}</td>
  //           </tr>`;
  // }

  // Fonction pour formater une date ou retourner une chaîne vide pour des valeurs spécifiques
  function formatDateOrEmpty(date) {
    if (
      formaterDate(date) === "01/01/1970" ||
      formaterDate(date) === "01/01/1900" ||
      date === ""
    ) {
      return "";
    }
    return formaterDate(date);
  }

  // Fonction pour retourner une valeur ou une chaîne vide si null ou un certain seuil
  function valueOrEmpty(value, defaultValue = "") {
    return value == null || value === "0" ? defaultValue : value;
  }

  // Fonction pour calculer la couleur de la commande
  function getCmdColor(detail) {
    if (detail.statut === "DISPO STOCK") {
      // return 'style="background-color: #c8ad7f; color: white;"';
      return { backgroundColor: "#c8ad7f", color: "white" };
    }
    if (["Error", "Back Order"].includes(detail.statut)) {
      // return 'style="background-color: red; color: white;"';
      return { backgroundColor: "red", color: "white" };
    }
    if (detail.Ord === "ORD") {
      // return 'style="background-color:#9ACD32; color: white;"';
      return { backgroundColor: "#9ACD32", color: "white" };
    }
    return {}; // Default case
  }

  // Fonction pour vérifier la réception partielle
  function getCmdColorRmq(detail) {
    // parseInt(detail.qteSolde) > 0 &&
    //parseInt(detail.qteSolde) !== parseInt(detail.qteQte)
    //   ? 'style="background-color: yellow;"'
    //   : "";
    return detail.qteSolde > 0 && detail.qteSolde !== detail.qteQte
      ? { backgroundColor: "yellow" }
      : {};
  }
});
