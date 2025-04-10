import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
import { changeTab, showTab } from "./pageNavigation";
import { updateDropdown } from "../../utils/selectionHandler";



document.addEventListener("DOMContentLoaded", function () {
  showTab(); // afficher la page d'article sélectionné par l'utilisateur

  /** Champs */

  // Tous les champs "Qté Dispo"
  document
    .querySelectorAll('[id*="proposition_qte_dispo_"]')
    .forEach((qtedispo) => {
      qtedispo.addEventListener("input", function () {
        qtedispo.value = qtedispo.value.replace(/[^\d]/g, "");
      });
    });
  // Tous les champs "Référence"
  document
    .querySelectorAll('[id*="proposition_reference_"]')
    .forEach((reference) => {
      reference.addEventListener("input", function () {
        reference.value = reference.value.toUpperCase();
      });
      autocompleteTheField(reference, "reference");
    });
  // Tous les champs "Fournisseur"
  document
    .querySelectorAll('[id*="proposition_fournisseur_"]')
    .forEach((fournisseur) => {
      fournisseur.addEventListener("input", function () {
        fournisseur.value = fournisseur.value.toUpperCase();
      });
      autocompleteTheField(fournisseur, "fournisseur");
    });
  // Tous les champs "Désignation"
  document
    .querySelectorAll('[id*="proposition_designation_"]')
    .forEach((designation) => {
      designation.addEventListener("input", function () {
        designation.value = designation.value.toUpperCase();
      });
      autocompleteTheField(designation, "designation");
    });

  document
    .querySelectorAll('[id*="proposition_codeFams1_"]')
    .forEach((famille) => {
      famille.addEventListener("change", function () {
        const numPage = localStorage.getItem("currentTab");
        const sousFamille = document.querySelector(
          "#demande_appro_proposition_codeFams2_" + numPage
        );
        if (famille.value !== "") {
          const spinnerElement = document.querySelector(
            "#spinner_codeFams2_" + numPage
          );
          const containerElement = document.querySelector(
            "#container_codeFams2_" + numPage
          );
          const designation = document.querySelector(
            "#demande_appro_proposition_designation_" + numPage
          );
          const fournisseur = document.querySelector(
            "#demande_appro_proposition_fournisseur_" + numPage
          );
          const reference = document.querySelector(
            "#demande_appro_proposition_reference_" + numPage
          );
          reset(fournisseur, reference, designation);

          updateDropdown(
            sousFamille,
            `api/demande-appro/sous-famille/${famille.value}`,
            "-- Choisir une sous-famille --",
            spinnerElement,
            containerElement
          );
        } else {
          resetDropdown(sousFamille, "-- Choisir une sous-famille --");
        }
      });
    });

  document
    .querySelectorAll('[id*="proposition_codeFams2_"]')
    .forEach((sousFamille) => {
      sousFamille.addEventListener("change", function () {
        const numPage = localStorage.getItem("currentTab");
        autocompleteTheFieldsPage(numPage);
      });
    });

  function autocompleteTheFieldsPage(numPage) {
    const designation = document.querySelector(
      "#demande_appro_proposition_designation_" + numPage
    );
    const fournisseur = document.querySelector(
      "#demande_appro_proposition_fournisseur_" + numPage
    );
    const reference = document.querySelector(
      "#demande_appro_proposition_reference_" + numPage
    );

    reset(fournisseur, reference, designation);

    autocompleteTheField(designation, "designation", numPage);
    autocompleteTheField(reference, "reference", numPage);
  }

  function reset(fournisseur, reference, designation) {
    fournisseur.value = "";
    reference.value = "";
    designation.value = "";
  }

  /** Boutons */

  // Tous les boutons "Précédent"
  document.querySelectorAll(".prevBtn").forEach((prevBtn) => {
    prevBtn.addEventListener("click", () => changeTab("prev"));
  });
  // Tous les boutons "Suivant"
  document.querySelectorAll(".nextBtn").forEach((nextBtn) => {
    nextBtn.addEventListener("click", () => changeTab("next"));
  });
  // Tous les boutons "Ajouter la référence"
  document.querySelectorAll('[id*="add_line_"]').forEach((addLine) => {
    addLine.addEventListener("click", () => ajouterReference(addLine.id));
  });

  document.getElementById("myForm").addEventListener("submit", function (e) {
    document.getElementById("child-prototype").remove();
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

let lastCheckedRadio = null;

function toggleRadio(radio) {
  if (lastCheckedRadio === radio) {
    radio.checked = false;
    lastCheckedRadio = null;
  } else {
    lastCheckedRadio = radio;
    const selectedValue = radio.value;
    console.log("Ligne sélectionnée :", selectedValue);

    // Rediriger vers une nouvelle URL avec le paramètre
    // window.location.href = `{{ App.base_path }}/demande-appro/proposition/${id}?ligne=${encodeURIComponent(
    //   selectedValue
    // )}`;
  }
}