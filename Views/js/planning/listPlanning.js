/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
const agenceDebiteurInput = document.querySelector(
    "#planning_search_agenceDebite"
  );
  const serviceDebiteurInput = document.querySelector(
    "#planning_search_serviceDebite"
  );
agenceDebiteurInput.addEventListener('change',selectAgence);

  function selectAgence() {
    serviceDebiteurInput.disabled = false;
    
    const agenceDebiteur = agenceDebiteurInput.value;
    let url = `/Hffintranet/serviceDebiteurPlanning-fetch/${agenceDebiteur}`;
    fetch(url)
      .then((response) => response.json())
      .then((services) => {
        console.log(services);

        // Effacer les éléments existants dans le conteneur
        serviceDebiteurInput.innerHTML = ""; 
      
        for (var i = 0; i < services.length; i++) {
          var div = document.createElement("div");
          div.className = "form-check";
      
          var checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.name = 'planning_search[serviceDebite][]';
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
        }
        
      })
      .catch((error) => console.error("Error:", error));
  }
  
  /** LIST DETAIL MODAL */

document.addEventListener("DOMContentLoaded", (event) => {
  const listeCommandeModal = document.getElementById("listeCommande");

  listeCommandeModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const id = button.getAttribute("data-id"); // Extract info from data-* attributes

    // Afficher le spinner et masquer le contenu des données
    document.getElementById("loading").style.display = "block";
    document.getElementById("dataContent").style.display = "none";

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

          // Masquer le spinner et afficher les données
          document.getElementById("loading").style.display = "none";
          document.getElementById("dataContent").style.display = "block";
        } else {
          // Si les données sont vides, afficher un message vide
          tableBody.innerHTML =
            '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
          document.getElementById("loading").style.display = "none";
          document.getElementById("dataContent").style.display = "block";
        }
      })
      .catch((error) => {
        const tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML =
          '<tr><td colspan="5">Could not retrieve data.</td></tr>';
        console.error("There was a problem with the fetch operation:", error);

        // Masquer le spinner même en cas d'erreur
        document.getElementById("loading").style.display = "none";
        document.getElementById("dataContent").style.display = "block";
      });
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    const tableBody = document.getElementById("commandesTableBody");
    tableBody.innerHTML = ""; // Vider le tableau
  });
});
  