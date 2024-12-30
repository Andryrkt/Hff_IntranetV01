/**
 * recuperer l'agence emetteur et changer le service emetteur selon l'agence
 */
const agenceEmetteurInput = document.querySelector(".agenceEmetteur");
const serviceEmetteurInput = document.querySelector(".serviceEmetteur");
const spinnerServiceEmetteur = document.getElementById(
  "spinner-service-emetteur"
);
const serviceContainerEmetteur = document.getElementById(
  "service-container-emetteur"
);

agenceEmetteurInput.addEventListener("change", selectAgenceEmetteur);

function selectAgenceEmetteur() {
  const agenceEmetteur = agenceEmetteurInput.value;

  if (DeleteContentService(agenceEmetteur, serviceEmetteurInput)) {
    return;
  }

  let url = `/Hffintranet/agence-fetch/${agenceEmetteur}`;
  toggleSpinner(spinnerServiceEmetteur, serviceContainerEmetteur, true);
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      updateServiceOptions(services, serviceEmetteurInput);
    })
    .catch((error) => console.error("Error:", error))
    .finally(() =>
      toggleSpinner(spinnerServiceEmetteur, serviceContainerEmetteur, false)
    );
}

/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
const spinnerServiceDebiteur = document.getElementById(
  "spinner-service-debiteur"
);
const serviceContainerDebiteur = document.getElementById(
  "service-container-debiteur"
);

agenceDebiteurInput.addEventListener("change", selectAgenceDebiteur);

function selectAgenceDebiteur() {
  const agenceDebiteur = agenceDebiteurInput.value;
  console.log(agenceDebiteur);

  // Efface les options si nécessaire, et sort si `agenceDebiteur` est vide
  if (DeleteContentService(agenceDebiteur, serviceDebiteurInput)) {
    return;
  }

  const url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  toggleSpinner(spinnerServiceDebiteur, serviceContainerDebiteur, true);

  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      updateServiceOptions(services, serviceDebiteurInput);
    })
    .catch((error) => console.error("Error:", error))
    .finally(() =>
      toggleSpinner(spinnerServiceDebiteur, serviceContainerDebiteur, false)
    );
}

/**
 * supprimer les options à une liste déroulante.
 * @param {HTMLElement} selectElement - Élément HTML de type <select> où on supprime les options.
 */
function supprimLesOptions(selectElement) {
  while (selectElement.options.length > 0) {
    selectElement.remove(0);
  }
}

/**
 * Ajoute une option par défaut à un élément <select>.
 * @param {HTMLSelectElement} selectElement - L'élément <select> cible.
 * @param {string} placeholder - Le texte affiché pour l'option par défaut.
 */
function optionParDefaut(selectElement, placeholder = "") {
  if (!(selectElement instanceof HTMLSelectElement)) {
    throw new Error("Le premier argument doit être un élément <select>.");
  }

  // Vérifier si une option par défaut existe déjà
  if (
    selectElement.options.length === 0 ||
    selectElement.options[0].value !== ""
  ) {
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = placeholder || " -- Choisir une option -- ";
    selectElement.add(defaultOption, 0); // Ajouter en première position
  }
}

function DeleteContentService(agenceValue, serviceInput) {
  if (agenceValue === "") {
    // Supprime toutes les options
    supprimLesOptions(serviceInput);

    // Ajoute l'option par défaut
    optionParDefaut(serviceInput, " -- Choisir une option -- ");

    // Indique qu'il faut sortir de la fonction appelante
    return true;
  } else {
    return false;
  }
}

function toggleSpinner(spinnerService, serviceContainer, show) {
  spinnerService.style.display = show ? "inline-block" : "none";
  serviceContainer.style.display = show ? "none" : "block";
}

/**
 * Ajoute des options à une liste déroulante.
 * @param {Array} optionsArray - Tableau contenant les objets avec les propriétés `value` et `text`.
 * @param {HTMLElement} selectElement - Élément HTML de type <select> où ajouter les options.
 */
function populateSelect(optionsArray, selectElement) {
  // Ajouter les nouvelles options
  for (var i = 0; i < optionsArray.length; i++) {
    var option = document.createElement("option");
    option.value = optionsArray[i].value;
    option.text = optionsArray[i].text;
    selectElement.add(option);
  }
}

function affichageValeurConsoleLog(selectElement) {
  for (var i = 0; i < selectElement.options.length; i++) {
    var option = selectElement.options[i];
    console.log("Value: " + option.value + ", Text: " + option.text);
  }
}

function updateServiceOptions(services, serviceInput) {
  // Supprimer toutes les options existantes
  supprimLesOptions(serviceInput);

  // Ajoute l'option par défaut
  optionParDefaut(serviceInput, " -- Choisir une option -- ");

  // Ajouter les nouvelles options
  populateSelect(services, serviceInput);

  //Afficher les nouvelles valeurs et textes des options
  affichageValeurConsoleLog(serviceInput);
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

document.addEventListener("DOMContentLoaded", (event) => {
  /** LIST COMMANDE MODAL */
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

  /** Docs à intégrer dans DW MODAL */

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
        console.log(docDansDw[0]);
        let docASoumettre = valeurDocASoumettre(docDansDw[0]);
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
      docDansDw.statut === "AFFECTEE SECTION"
    ) {
      docASoumettre = [{ value: "DEVIS", text: "DEVIS" }];
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

/**
 * sweetalert pur le bouron cloturer dit
 */
const clotureDit = document.querySelectorAll(".clotureDit");

clotureDit.forEach((el) => {
  el.addEventListener("click", (e) => {
    e.preventDefault();
    let id = el.getAttribute("data-id");

    Swal.fire({
      title: "êtes-vous sur?",
      text: "cette action est irreversible",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "OUI",
    }).then(() => {
      window.location.href = `/Hffintranet/cloturer-annuler/${id}`;
    });
    // .then((result) => {
    //   if (result.isConfirmed) {
    //     Swal.fire({
    //       title: "Changement de statut!",
    //       text: "en CLOTUREE ANNULEE",
    //       icon: "success",
    //     })
    // .then(() => {
    //       window.location.href = `/Hffintranet/cloturer-annuler/${id}`;
    //     });
    //   }
    // });
  });
});
