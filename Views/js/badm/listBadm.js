import { FetchManager } from "../api/FetchManager";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

/**
 * recuperer l'agence emetteur et changer le service emetteur selon l'agence
 */
const agenceEmetteurInput = document.querySelector(".agenceEmetteur");
const serviceEmetteurInput = document.querySelector(".serviceEmetteur");

if (agenceEmetteurInput) {
  agenceEmetteurInput.addEventListener("change", selectAgenceEmetteur);
}

function selectAgenceEmetteur() {
  if (!agenceEmetteurInput || !serviceEmetteurInput) {
    console.warn(
      "Éléments agenceEmetteurInput ou serviceEmetteurInput non trouvés"
    );
    return;
  }

  const agenceEmetteur = agenceEmetteurInput.value;

  if (agenceEmetteur === "") {
    while (serviceEmetteurInput.options.length > 0) {
      serviceEmetteurInput.remove(0);
    }
  }

  let url = `api/agence-fetch/${agenceEmetteur}`;
  fetchManager
    .get(url)
    .then((services) => {
      console.log(services);

      // Supprimer toutes les options existantes
      while (serviceEmetteurInput.options.length > 0) {
        serviceEmetteurInput.remove(0);
      }

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

if (agenceDebiteurInput) {
  agenceDebiteurInput.addEventListener("change", selectAgenceDebiteur);
}

function selectAgenceDebiteur() {
  if (!agenceDebiteurInput || !serviceDebiteurInput) {
    console.warn(
      "Éléments agenceDebiteurInput ou serviceDebiteurInput non trouvés"
    );
    return;
  }

  const agenceDebiteur = agenceDebiteurInput.value;

  if (agenceDebiteur === "") {
    while (serviceDebiteurInput.options.length > 0) {
      serviceDebiteurInput.remove(0);
    }

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = " -- Choisir une service -- ";
    serviceDebiteurInput.add(defaultOption);
    return; // Sortir de la fonction
  }

  let url = `api/agence-fetch/${agenceDebiteur}`;
  fetchManager
    .get(url)
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
/** FIN AGENCE SERVICE */
