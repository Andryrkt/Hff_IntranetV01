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
    .catch((error) => console.error("Error:", error));
}

/**
 * recuperer l'agence et changer le service selon l'agence
 */
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
console.log(serviceDebiteurInput);
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
        if (i < services.length) {
          var option = serviceDebiteurInput.options[i];
          option.value = services[i].value;
          option.text = services[i].text;
        }
      }

      // Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
        var option = serviceDebiteurInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error));
}
