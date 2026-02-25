import { FetchManager } from "../api/FetchManager";
import { filterServiceByAgence } from "../utils/agenceService/filterServiceByAgence.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

filterServiceByAgence({
  agenceSelector: "#badm_search_agenceEmetteur",
  serviceSelector: "#badm_search_serviceEmetteur",
});

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
