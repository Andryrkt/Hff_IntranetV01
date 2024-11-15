
/**
 * recupérer le catégorie et afficher les sous catégorie et autre categorie correspondant
 */
const categorieInput = document.querySelector(".categorie");
const sousCategorieInput = document.querySelector(".sous-categorie");
const autreCategorieInput = document.querySelector(".autre-categorie");

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

