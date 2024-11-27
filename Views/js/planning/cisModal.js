/**
 * POUR LE CIS
 */
const listecisModal = document.getElementById("cisPlanning");
listecisModal.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const orIntv = button.getAttribute("data-id"); // Extract info from data-* attributes
  const numDit = button.getAttribute("data-numDit");
  const migration = button.getAttribute("data-migration");
  console.log(migration);

  // Mettre à jour le lien avec le numDit dynamique
  const dossierDitLink = document.getElementById("dossierDitLink");
  if (migration == "1") {
    console.log(dossierDitLink);
    dossierDitLink.style.display = "none";
  }
  //console.log(numDit);
  //console.log(dossierDitLink);
  dossierDitLink.onclick = (event) => {
    event.preventDefault();
    window.open(
      `/Hffintranet/dw-intervention-atelier-avec-dit/${numDit}`,
      "_blank"
    );
  };

  afficheSpinner();

  fetchDetailModalCis(orIntv);

  const numOr = orIntv.split("-")[0];
  const numItv = orIntv.split("-")[1];
  //console.log(numOr, numItv);

  fetchTechnicienInterv(numOr, numItv);
});

// Gestionnaire pour la fermeture du modal
listecisModal.addEventListener("hidden.bs.modal", function () {
  const tableBody = document.getElementById("cisTableBody");
  tableBody.innerHTML = ""; // Vider le tableau
});

function afficheSpinner() {
  // Afficher le spinner et masquer le contenu des données
  document.getElementById("loadingcis").style.display = "block";
  document.getElementById("dataContentcis").style.display = "none";
}

function masquerSpinner() {
  // Masquer le spinner et afficher les données
  document.getElementById("loadingcis").style.display = "none";
  document.getElementById("dataContentcis").style.display = "block";
}

function fetchTechnicienInterv(numOr, numItv) {
  fetch(`/Hffintranet/api/technicien-intervenant/${numOr}/${numItv}`)
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

        masquerSpinner();
      } else {
        // Si les données sont vides, afficher un message vide
        tableBody.innerHTML =
          '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
        masquerSpinner();
      }
    })
    .catch((error) => {
      const tableBody = document.getElementById("technicienTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="5">Could not retrieve data.</td></tr>';
      console.error("There was a problem with the fetch operation:", error);
      masquerSpinner();
    });
}

function fetchDetailModalCis(id) {
  // Fetch request to get the data
  fetch(`/Hffintranet/detail-modal/${id}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      const tableBody = document.getElementById("cisTableBody");
      const Ornumcis = document.getElementById("orIntvcis");
      console.log(tableBody, Ornumcis);

      tableBody.innerHTML = ""; // Clear previous data

      if (data.length > 0) {
        data.forEach((detail) => {
          console.log(detail);

          Ornumcis.innerHTML = `${detail.numor} - ${detail.intv}`;

          // Formater la date
          let dateEtaIvato;
          let dateMagasin;
          let dateStatut;
          let numCde;
          var numCis;
          let statrmq;
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
          if (detail.numcis == "0") {
            numCis = "";
          } else {
            numCis = detail.numcis;
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
          } else {
            cmdColor = "";
          }

          // Affichage
          let row = `<tr>
                      <td>${detail.numor}</td> 
                      <td>${detail.intv}</td> 
                      <td>${numCis}</td> 
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
      const tableBody = document.getElementById("cisTableBody");
      tableBody.innerHTML =
        '<tr><td colspan="5">Could not retrieve data.</td></tr>';
      console.error("There was a problem with the fetch operation:", error);

      masquerSpinner();
    });

  function formaterDate(daty) {
    const date = new Date(daty);
    return `${date.getDate().toString().padStart(2, "0")}/${(
      date.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}/${date.getFullYear()}`;
  }
}
