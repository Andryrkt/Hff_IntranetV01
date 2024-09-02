/**
 * recuperer l'agence emetteur et changer le service emetteur selon l'agence
 */
const agenceEmetteurInput = document.querySelector(".agenceEmetteur");
const serviceEmetteurInput = document.querySelector(".serviceEmetteur");

agenceEmetteurInput.addEventListener("change", selectAgenceEmetteur);

function selectAgenceEmetteur() {
  const agenceDebiteur = agenceEmetteurInput.value;

  if (agenceDebiteur === "") {
    while (serviceEmetteurInput.options.length > 0) {
      serviceEmetteurInput.remove(0);
    }

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = " -- Choisir une service -- ";
    serviceEmetteurInput.add(defaultOption);
    return; // Sortir de la fonction
  }

  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);

      // Supprimer toutes les options existantes
      while (serviceEmetteurInput.options.length > 0) {
        serviceEmetteurInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une service -- ";
      serviceEmetteurInput.add(defaultOption);

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < services.length; i++) {
        var option = document.createElement("option");
        option.value = services[i].value;
        option.text = services[i].text;
        serviceEmetteurInput.add(option);
      }

      //Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < serviceEmetteurInput.options.length; i++) {
        var option = serviceEmetteurInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error));
}

/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");

agenceDebiteurInput.addEventListener("change", selectAgenceDebiteur);

function selectAgenceDebiteur() {
  const agenceDebiteur = agenceDebiteurInput.value;

  if (agenceDebiteur === "") {
    while (serviceEmetteurInput.options.length > 0) {
      serviceEmetteurInput.remove(0);
    }

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = " -- Choisir une service -- ";
    serviceEmetteurInput.add(defaultOption);
    return; // Sortir de la fonction
  }

  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);

      // Supprimer toutes les options existantes
      while (serviceDebiteurInput.options.length > 0) {
        serviceDebiteurInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une service -- ";
      serviceDebiteurInput.add(defaultOption);

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < services.length; i++) {
        var option = document.createElement("option");
        option.value = services[i].value;
        option.text = services[i].text;
        serviceDebiteurInput.add(option);
      }

      //Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
        var option = serviceDebiteurInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error));
}

/**
 * CREATION D'EXCEL
 */
const typeDocumentInput = document.querySelector("#dit_search_typeDocument");
const niveauUrgenceInput = document.querySelector("#dit_search_niveauUrgence");
const statutInput = document.querySelector("#dit_search_statut");
const idMaterielInput = document.querySelector("#dit_search_idMateriel");
const interExternInput = document.querySelector("#dit_search_internetExterne");
const dateDemandeDebutInput = document.querySelector("#dit_search_dateDebut");
const dateDemandeFinInput = document.querySelector("#dit_search_dateFin");
const buttonExcelInput = document.querySelector("#excelDit");
buttonExcelInput.addEventListener("click", recherche);

function recherche() {
  const typeDocument = typeDocumentInput.value;
  const niveauUrgence = niveauUrgenceInput.value;
  const statut = statutInput.value;
  const idMateriel = idMaterielInput.value;
  const interExtern = interExternInput.value;
  const dateDemandeDebut = dateDemandeDebutInput.value;
  const dateDemandeFin = dateDemandeFinInput.value;

  let url = "/Hffintranet/dit-excel";

  const data = {
    idMateriel: idMateriel || null,
    typeDocument: typeDocument || null,
    niveauUrgence: niveauUrgence || null,
    statut: statut || null,
    interExtern: interExtern || null,
    dateDebut: dateDemandeDebut || null,
    dateFin: dateDemandeFin || null,
  };

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

/** LIST COMMANDE MODAL */

document.addEventListener("DOMContentLoaded", (event) => {
  const listeCommandeModal = document.getElementById("listeCommande");
  listeCommandeModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const id = button.getAttribute("data-id"); // Extract info from data-* attributes

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
        console.log(tableBody);

        tableBody.innerHTML = ""; // Clear previous data

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

          //affichage
          let row = `<tr>
                      <td>${command.slor_numcf}</td> 
                      <td>${formattedDate}</td>
                      <td> ${typeCommand}</td>
                      <td> ${command.fcde_posc}</td>
                      <td> ${command.fcde_posl}</td>
                  </tr>`;
          tableBody.innerHTML += row;
        });
      })
      .catch((error) => {
        var tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML =
          '<tr><td colspan="3">Could not retrieve data.</td></tr>';
        console.error("There was a problem with the fetch operation:", error);
      });
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    const tableBody = document.getElementById("commandesTableBody");
    tableBody.innerHTML = ""; // Vider le tableau
  });

  function formatDate(date) {
    const date = new Date(date);
    return `${date.getDate().toString().padStart(2, "0")}/${(
      date.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}/${date.getFullYear()}`;
  }
});
