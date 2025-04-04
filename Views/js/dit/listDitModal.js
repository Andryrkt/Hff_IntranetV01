import { FetchManager } from "../api/FetchManager";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

/** SECTION AFFECTER MODAL */
const sectionAffecteeModal = document.getElementById("sectionAffectee");

sectionAffecteeModal.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const id = button.getAttribute("data-id"); // Extract info from data-* attributes
  const loadingAffectee = document.getElementById("loadingAffectee");
  const dataContentAffecter = document.getElementById("dataContentAffectee");
  // Afficher le spinner et masquer le contenu des données
  loadingAffectee.style.display = "block";
  dataContentAffecter.style.display = "none";

  // Fetch request to get the data
  fetchManager
    .get(`section-affectee-modal-fetch/${id}`)
    .then((data) => {
      const tableBody = document.getElementById("AffecteeTableBody");
      tableBody.innerHTML = ""; // Clear previous data
      console.log(data);

      if (data.length > 0) {
        // Générer les lignes du tableau en fonction des données
        data.forEach((item) => {
          let row = `<tr>
                        <td class="fw-bold">${item.sectionAffectee}</td>
                        <td>${
                          item.sectionSupport1 !== null
                            ? item.sectionSupport1
                            : "--"
                        }</td>
                        <td>${
                          item.sectionSupport2 !== null
                            ? item.sectionSupport2
                            : "--"
                        }</td>
                        <td>${
                          item.sectionSupport3 !== null
                            ? item.sectionSupport3
                            : "--"
                        }</td>
                     </tr>`;
          tableBody.innerHTML += row;
        });
      } else {
        // Si aucune donnée n'est disponible
        tableBody.innerHTML =
          '<tr><td colspan="3">Aucune donnée disponible.</td></tr>';
      }

      // Masquer le spinner et afficher les données
      loadingAffectee.style.display = "none";
      dataContentAffecter.style.display = "block";
    })
    .catch((error) => {
      const tableBody = document.getElementById("AffecteeTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="3">On ne peut pas récupérer les données</td></tr>';
      console.error("There was a problem with the fetch operation:", error);

      // Masquer le spinner même en cas d'erreur
      loadingAffectee.style.display = "none";
      dataContentAffecter.style.display = "block";
    });
});

// Gestionnaire pour la fermeture du modal
sectionAffecteeModal.addEventListener("hidden.bs.modal", function () {
  const tableBody = document.getElementById("AffecteeTableBody");
  tableBody.innerHTML = ""; // Vider le tableau
});

/** ============================================== 
 *  Facturation modal
 * 
=================================================*/
const facturationModalInput = document.getElementById("facturation");

facturationModalInput.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const id = button.getAttribute("data-id"); // Extract info from data-* attributes
  const loadingfacture = document.getElementById("loadingfacture");
  const dataContentfacture = document.getElementById("dataContentfacture");
  // Afficher le spinner et masquer le contenu des données
  loadingfacture.style.display = "block";
  dataContentfacture.style.display = "none";

  // Fetch request to get the data
  fetchManager
    .get(`facturation-fetch/${id}`)
    .then((data) => {
      const tableBody = document.getElementById("facturationBody");
      tableBody.innerHTML = ""; // Clear previous data
      console.log(data);

      if (data.length > 0) {
        // Générer les lignes du tableau en fonction des données
        data.forEach((item) => {
          // Vérifier si le statut est vide ou null
          let rowClass = item.statut == "-" ? "textColor" : "";

          // Créer la ligne du tableau
          let row = `<tr>
                      <td class="${rowClass}">${item.numeroItv}</td>
                      <td class="${rowClass}">${item.numeroFact}</td>
                      <td class="${rowClass}">${item.statut}</td>
                    </tr>`;

          tableBody.innerHTML += row;
        });
      } else {
        // Si aucune donnée n'est disponible
        tableBody.innerHTML =
          '<tr><td colspan="3">Aucune donnée disponible.</td></tr>';
      }

      // Masquer le spinner et afficher les données
      loadingfacture.style.display = "none";
      dataContentfacture.style.display = "block";
    })
    .catch((error) => {
      const tableBody = document.getElementById("AffecteeTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="3">On ne peut pas récupérer les données</td></tr>';
      console.error("There was a problem with the fetch operation:", error);

      // Masquer le spinner même en cas d'erreur
      loadingfacture.style.display = "none";
      dataContentfacture.style.display = "block";
    });
});

// Gestionnaire pour la fermeture du modal
facturationModalInput.addEventListener("hidden.bs.modal", function () {
  const tableBody = document.getElementById("facturationBody");
  tableBody.innerHTML = ""; // Vider le tableau
});

/** ============================================== 
 *  ri MODAL
 * 
=================================================*/
const riModalInput = document.getElementById("ri");

riModalInput.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const id = button.getAttribute("data-id"); // Extract info from data-* attributes
  const loadingri = document.getElementById("loadingri");
  const dataContentri = document.getElementById("dataContentri");
  // Afficher le spinner et masquer le contenu des données
  loadingri.style.display = "block";
  dataContentri.style.display = "none";
  console.log(id);

  // Fetch request to get the data
  fetchManager
    .get(`ri-fetch/${id}`)
    .then((data) => {
      const tableBody = document.getElementById("riBody");
      tableBody.innerHTML = ""; // Clear previous data

      if (data.length > 0) {
        // Générer les lignes du tableau en fonction des données
        data.forEach((item) => {
          // Vérifier si le statut est vide ou null
          let risoumis = item.riSoumis
            ? `<i class="fa-solid fa-check"></i>`
            : "";
          let rowClass = item.riSoumis ? "" : "textColor";

          // Créer la ligne du tableau
          let row = `<tr>
                      <td>${risoumis}</td>
                      <td class="${rowClass}">${item.numeroitv}</td>
                      <td class="${rowClass}">${
            item.commentair ? item.commentair : "-"
          }</td>
                    </tr>`;

          tableBody.innerHTML += row;
        });
      } else {
        // Si aucune donnée n'est disponible
        tableBody.innerHTML =
          '<tr><td colspan="3">Aucune donnée disponible.</td></tr>';
      }

      // Masquer le spinner et afficher les données
      loadingri.style.display = "none";
      dataContentri.style.display = "block";
    })
    .catch((error) => {
      const tableBody = document.getElementById("AffecteeTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="3">On ne peut pas récupérer les données</td></tr>';
      console.error("There was a problem with the fetch operation:", error);

      // Masquer le spinner même en cas d'erreur
      loadingri.style.display = "none";
      dataContentri.style.display = "block";
    });
});

// Gestionnaire pour la fermeture du modal
riModalInput.addEventListener("hidden.bs.modal", function () {
  const tableBody = document.getElementById("riBody");
  tableBody.innerHTML = ""; // Vider le tableau
});
