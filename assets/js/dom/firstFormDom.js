import "select2/dist/css/select2.min.css"; // Styles de base
import "../../css/dom/firstFormDom.css";
import $ from "jquery";
import "select2";
import { FetchManager } from "../api/FetchManager";

const fetchManager = new FetchManager();

document.addEventListener("DOMContentLoaded", () => {
  const nom = document.querySelector("#dom_form1_nom");
  const prenom = document.querySelector("#dom_form1_prenom");
  const cin = document.querySelector("#dom_form1_cin");
  const salarier = document.querySelector("#dom_form1_salarie");
  const matriculeNomInput = document.querySelector("#dom_form1_matriculeNom");
  const matriculeInput = document.querySelector("#dom_form1_matricule");
  const sousTypeDocument = document.querySelector(
    "#dom_form1_sousTypeDocument"
  );
  const agenceInput = document.querySelector("#dom_form1_agenceEmetteur");
  const categorie = document.querySelector("#dom_form1_categorie");

  function toggleFields() {
    const isTemporaire = salarier.value === "TEMPORAIRE";
    [nom, prenom, cin].forEach((field) => {
      field.parentElement.style.display = isTemporaire ? "block" : "none";
      field.disabled = !isTemporaire;
    });

    [matriculeNomInput, matriculeInput].forEach((field) => {
      field.parentElement.style.display = isTemporaire ? "none" : "block";
      field.disabled = isTemporaire;
    });
  }

  salarier.addEventListener("change", toggleFields);
  toggleFields();

  function changementSelon() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    const codeAgence = agenceInput.value.split(" ")[0];

    if (
      (sousTypeDocumentValue !== "5" &&
        sousTypeDocumentValue !== "2" &&
        codeAgence !== "50") ||
      (sousTypeDocumentValue !== "2" && codeAgence === "50")
    ) {
      categorie.parentElement.style.display = "none";
    } else {
      categorie.parentElement.style.display = "block";
      selectCategorie();
    }
  }

  function selectCategorie() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    const url = `categorie-fetch/${sousTypeDocumentValue}`;

    fetchManager
      .get(url)
      .then((categories) => {
        categorie.innerHTML = "";
        categories.forEach((cat) => {
          const option = new Option(cat.description, cat.id);
          categorie.add(option);
        });
      })
      .catch((error) => console.error("Error:", error));
  }

  sousTypeDocument.addEventListener("change", changementSelon);
  changementSelon();

  $("#dom_form1_matriculeNom").select2({
    width: "100%",
    placeholder: "-- choisir une personnel --",
  });

  $("#dom_form1_matriculeNom").on("select2:select", function () {
    const matriculeNom = $("#dom_form1_matriculeNom option:selected").text();
    matriculeInput.value = matriculeNom.slice(0, 4);
  });
});
