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
 * recupérer le catégorie et afficher les sous catégorie correspondant
 */
const categorieInput = document.querySelector(".categorie");
const sousCategorieInput = document.querySelector(".sous-categorie");
console.log(categorieInput, sousCategorieInput);

categorieInput.addEventListener("change", selectCategorieSousCategorie);

function selectCategorieSousCategorie() {
  const categorie = categorieInput.value;

  if (categorie === "") {
    while (sousCategorieInput.options.length > 0) {
      sousCategorieInput.remove(0);
    }

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = " -- Choisir une sous catégorie -- ";
    sousCategorieInput.add(defaultOption);
    return; // Sortir de la fonction
  }

  console.log(categorie);
}
