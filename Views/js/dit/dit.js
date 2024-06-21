/**recupère l'idMateriel et afficher les information du matériel */
const idMaterielInput = document.querySelector(".idMateriel");
const numParcInput = document.querySelector(".numParc");
const numSerieInput = document.querySelector(".numSerie");
const constructeurInput = document.querySelector("#constructeur");
const designationInput = document.querySelector("#designation");
const modelInput = document.querySelector("#model");
const casierInput = document.querySelector("#casier");
const kmInput = document.querySelector("#km");
const heuresInput = document.querySelector("#heures");

idMaterielInput.addEventListener("blur", InfoMateriel);
numParcInput.addEventListener("blur", InfoMateriel);
numSerieInput.addEventListener("blur", InfoMateriel);

function InfoMateriel() {
  const idMateriel = idMaterielInput.value;
  const numParc = numParcInput.value;
  const numSerie = numSerieInput.value;
  const erreur = document.querySelector("#erreur");
  const condition =
    (idMateriel !== "" && idMateriel !== null && idMateriel !== undefined) ||
    (numParc !== "" && numParc !== null && numParc !== undefined) ||
    (numSerie !== "" && numSerie !== null && numSerie !== undefined);
  if (condition) {
    erreur.innerHTML = "";
    let url = "/Hffintranet/fetch-materiel";

    if (idMateriel) {
      url += `/${idMateriel}`;
    } else {
      url += "/0"; // Ajoutez un slash pour éviter les erreurs de format d'URL
    }

    if (numParc) {
      url += `/${numParc}`;
    } else if (!idMateriel) {
      url += "/0"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
    }

    if (numSerie) {
      url += `/${numSerie}`;
    } else if (!numParc && !idMateriel) {
      url += "/"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
    }
    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        //   idMaterielInput.value = data[0].num_matricule;
        //   numParcInput.value = data[0].num_parc;
        //   numSerieInput.value = data[0].num_serie;

        constructeurInput.innerHTML = data[0].constructeur;
        designationInput.innerHTML = data[0].designation;
        modelInput.innerHTML = data[0].modele;
        casierInput.innerHTML = data[0].casier_emetteur;
        kmInput.innerHTML = data[0].km;
        heuresInput.innerHTML = data[0].heure;
      })
      .catch((error) => {
        if (error instanceof SyntaxError) {
          erreur.innerHTML =
            "Erreur : l'information du matériel n'est pas dans la base de données.";
          constructeurInput.innerHTML = "";
          designationInput.innerHTML = "";
          modelInput.innerHTML = "";
          casierInput.innerHTML = "";
          kmInput.innerHTML = "";
          heuresInput.innerHTML = "";
        } else {
          console.error("Error:", error);
          erreur.innerHTML = "Erreur : " + error.message;
        }
      });
  } else {
    erreur.innerHTML = "veuillez completer l'un des champs ";
  }
}

/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);

      // Supprimer toutes les options existantes
      while (serviceDebiteurInput.options.length > 0) {
        serviceDebiteurInput.remove(0);
      }

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
 * CHAMP CLIENT MISE EN MAJUSCULE
 */
const nomClientInput = document.querySelector(".nomClient");

nomClientInput.addEventListener("input", MiseMajuscule);

function MiseMajuscule() {
  nomClientInput.value = nomClientInput.value.toUpperCase();
}

/**
 * INTERNE - EXTERNE
 */
const interneExterneInput = document.querySelector(".interneExterne");
const numTelInput = document.querySelector(".numTel");
const clientSousContratInput = document.querySelector(".clientSousContrat");

console.log(numTelInput, clientSousContratInput);

if (interneExterneInput.value === "INTERNE") {
  nomClientInput.setAttribute("disabled", true);
  numTelInput.setAttribute("disabled", true);
  clientSousContratInput.setAttribute("disabled", true);
}

interneExterneInput.addEventListener("change", interneExterne);
function interneExterne() {
  if (interneExterneInput.value === "EXTERNE") {
    nomClientInput.removeAttribute("disabled");
    numTelInput.removeAttribute("disabled");
    clientSousContratInput.removeAttribute("disabled");
    agenceDebiteurInput.setAttribute("disabled", true);
    serviceDebiteurInput.setAttribute("disabled", true);
  } else {
    nomClientInput.setAttribute("disabled", true);
    numTelInput.setAttribute("disabled", true);
    clientSousContratInput.setAttribute("disabled", true);
  }
}
