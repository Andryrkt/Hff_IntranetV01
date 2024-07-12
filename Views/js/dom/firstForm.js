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
    if (sousTypeDocumentValue !== "10" && sousTypeDocumentValue !== "2") {
      categorie.parentElement.style.display = "none";
    } else {
      categorie.parentElement.style.display = "block";
    }
  }
});
