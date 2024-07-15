document.addEventListener("DOMContentLoaded", (event) => {
  /**
   * CACHE ET AFFICHE (nom, prenom, cin) SELON LE SALARIE (Temporaire ou permanant)
   */
  const nom = document.querySelector("#dom_form1_nom");
  const prenom = document.querySelector("#dom_form1_prenom");
  const cin = document.querySelector("#dom_form1_cin");
  const salarier = document.querySelector("#dom_form1_salarie");

  function toggleFields() {
    if (salarier.value === "TEMPORAIRE") {
      nom.parentElement.style.display = "block";
      prenom.parentElement.style.display = "block";
      cin.parentElement.style.display = "block";
    } else {
      nom.parentElement.style.display = "none";
      prenom.parentElement.style.display = "none";
      cin.parentElement.style.display = "none";
    }
  }

  salarier.addEventListener("change", toggleFields);
  toggleFields();

  /**
   * AFFICHE champ CATEGORIE selon le TYPE DE MISSION
   */
  const sousTypeDocument = document.querySelector(
    "#dom_form1_sousTypeDocument"
  );

  const categorie = document.querySelector("#dom_form1_categorie");

  sousTypeDocument.addEventListener("change", changementSelon);

  function changementSelon() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    console.log(sousTypeDocumentValue);
    if (sousTypeDocumentValue !== "5" && sousTypeDocumentValue !== "2") {
      categorie.parentElement.style.display = "none";
    } else {
      categorie.parentElement.style.display = "block";
      selectCategorie();
    }
  }

  function selectCategorie() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    let url = `/Hffintranet/categorie-fetch/${sousTypeDocumentValue}`;
    fetch(url)
      .then((response) => response.json())
      .then((categories) => {
        console.log(categories);

        //Supprimer toutes les options existantes
        while (categorie.options.length > 0) {
          categorie.remove(0);
        }

        //Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < categories.length; i++) {
          var option = document.createElement("option");
          option.value = categories[i].value;
          option.text = categories[i].text;
          categorie.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < categorie.options.length; i++) {
          var option = categorie.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  /**
   * AFFICHER LE MATRICULE SELON le Matricule et Nom Choisie
   *
   */

  $("#dom_form1_matriculeNom").select2({
    width: "100%", // Optionnel : ajustez la largeur selon vos besoins
    placeholder: "-- choisir une personnel --",
  });

  // Sélectionner les éléments de formulaire
  const matriculeNomInput = document.querySelector("#dom_form1_matriculeNom");
  const matriculeInput = document.querySelector("#dom_form1_matricule");

  // Ajouter un écouteur d'événement pour Select2
  $("#dom_form1_matriculeNom").on("select2:select", function (e) {
    changeMatricule(e.params.data.id); // Passer l'id sélectionné à la fonction
  });

  function changeMatricule(matriculeNom) {
    console.log(matriculeNom);
    let url = `/Hffintranet/matricule-fetch/${matriculeNom}`;
    fetch(url)
      .then((response) => response.json())
      .then((matricule) => {
        console.log(matricule.Matricule);
        matriculeInput.value = matricule.Matricule;
      })
      .catch((error) => console.error("Error:", error));
  }
});
