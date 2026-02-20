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

  let url = `/Hffintranet/api/agence-fetch/${agenceDebiteur}`;
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

  let url = `/Hffintranet/api/agence-fetch/${agenceDebiteur}`;
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
 * recupérer le catégorie et afficher les sous catégorie et autre categorie correspondant
 */
const categorieInput = document.querySelector(".categorie");
const sousCategorieInput = document.querySelector(".sous-categorie");
const autreCategorieInput = document.querySelector(".autres-categories");

//AFFICHAGE SOUS CATEGORIES
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

  let url = `/Hffintranet/api/sous-categorie-fetch/${categorie}`;
  fetch(url)
    .then((response) => response.json())
    .then((sousCategories) => {
      console.log(sousCategories);

      // Supprimer toutes les options existantes
      while (sousCategorieInput.options.length > 0) {
        sousCategorieInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une sous catégorie -- ";
      sousCategorieInput.add(defaultOption);

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < sousCategories.length; i++) {
        var option = document.createElement("option");
        option.value = sousCategories[i].value;
        option.text = sousCategories[i].text;
        sousCategorieInput.add(option);
      }

      //Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < sousCategorieInput.options.length; i++) {
        var option = sousCategorieInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error));

  //AFFICHAGE AUTRES CATEGORIE
  sousCategorieInput.addEventListener(
    "change",
    selectSousCategorieAutresCategories
  );

  function selectSousCategorieAutresCategories() {
    const sousCategorie = sousCategorieInput.value;

    if (sousCategorie === "") {
      while (autreCategorieInput.options.length > 0) {
        autreCategorieInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une sous catégorie -- ";
      autreCategorieInput.add(defaultOption);
      return; // Sortir de la fonction
    }

    console.log(sousCategorie);

    let url = `/Hffintranet/api/autres-categorie-fetch/${sousCategorie}`;
    fetch(url)
      .then((response) => response.json())
      .then((autresCategories) => {
        console.log(autresCategories);

        // Supprimer toutes les options existantes
        while (autreCategorieInput.options.length > 0) {
          autreCategorieInput.remove(0);
        }

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.text = " -- Choisir une autre categorie-- ";
        autreCategorieInput.add(defaultOption);

        // Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < autresCategories.length; i++) {
          var option = document.createElement("option");
          option.value = autresCategories[i].value;
          option.text = autresCategories[i].text;
          autreCategorieInput.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < autreCategorieInput.options.length; i++) {
          var option = autreCategorieInput.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));
  }
}

/**
 * modal pour la modification d'un ticket
 */
document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner le lien et le modal
  const modifierLink = document.getElementById("modifierLink");
  const modal = new bootstrap.Modal(
    document.getElementById("confirmationModal")
  );
  const confirmModification = document.getElementById("confirmModification");

  // Variable pour stocker l'URL de la redirection
  let redirectUrl = "";

  // Ajouter un événement de clic sur le lien
  modifierLink.addEventListener("click", function (event) {
    // Empêcher la redirection initiale
    event.preventDefault();

    // Sauvegarder l'URL pour la redirection après confirmation
    redirectUrl = this.getAttribute("href");

    // Afficher le modal
    modal.show();
  });

  // Ajouter un événement de clic sur le bouton "Confirmer"
  confirmModification.addEventListener("click", function () {
    // Effectuer la redirection après confirmation
    window.location.href = redirectUrl;
  });
});
