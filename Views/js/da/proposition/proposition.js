import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
import { changeTab, showTab } from "./pageNavigation";
import { updateDropdown } from "../../utils/selectionHandler";

document.addEventListener("DOMContentLoaded", function () {
  showTab(); // afficher la page d'article sélectionné par l'utilisateur

  // const numPage = localStorage.getItem("currentTab");

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

  // Tous les champs "Famille"
  document
    .querySelectorAll('[id*="proposition_codeFams1_"]')
    .forEach((familleInput) => {
      const numPage = familleInput.id.split("_").pop();

      initialiserFamilleEtSousFamille(numPage, familleInput);
      gererChangementFamille(numPage, familleInput);
    });

  //Tous les champs sous famille
  document
    .querySelectorAll('[id*="proposition_codeFams2_"]')
    .forEach((sousFamille) => {
      const numPage = sousFamille.id.split("_").pop();
      const { fournisseur, reference, designation } = recupInput(numPage);

      sousFamille.addEventListener("change", function () {
        reset(fournisseur, reference, designation);

        autocompleteTheFieldsPage(numPage);
      });
    });

  function initialiserFamilleEtSousFamille(numPage, familleInput) {
    const { sousFamilleInput, codeFamilleInput, codeSousFamilleInput } =
      recupInput(numPage);

    const defaultFamille = codeFamilleInput?.value;
    const defaultSousFamille = codeSousFamilleInput?.value;
    console.log(defaultFamille, defaultSousFamille);

    if (defaultFamille) {
      familleInput.value = defaultFamille;
      familleInput.dispatchEvent(new Event("change"));
    }

    if (sousFamilleInput && defaultSousFamille) {
      setTimeout(() => {
        sousFamilleInput.value = defaultSousFamille;
        sousFamilleInput.dispatchEvent(new Event("change"));
      }, 300);
    }
  }

  function gererChangementFamille(numPage, familleInput) {
    const {
      sousFamilleInput,
      fournisseur,
      reference,
      designation,
      spinnerElement,
      containerElement,
    } = recupInput(numPage);

    familleInput.addEventListener("change", function () {
      if (familleInput.value !== "") {
        reset(fournisseur, reference, designation);

        updateDropdown(
          sousFamilleInput,
          `api/demande-appro/sous-famille/${familleInput.value}`,
          "-- Choisir une sous-famille --",
          spinnerElement,
          containerElement
        );
      } else {
        resetDropdown(sousFamilleInput, "-- Choisir une sous-famille --");
      }
    });
  }

  /**
   * Permet de récupérer les éléments HTML liés à une page/index spécifique
   * @param {string|number} numPage
   * @returns {object} - Un objet contenant tous les éléments utiles
   */
  function recupInput(numPage) {
    return {
      sousFamilleInput: document.querySelector(
        `#demande_appro_proposition_codeFams2_${numPage}`
      ),
      codeFamilleInput: document.querySelector(`#codeFams1_${numPage}`),
      codeSousFamilleInput: document.querySelector(`#codeFams2_${numPage}`),
      spinnerElement: document.querySelector(`#spinner_codeFams2_${numPage}`),
      containerElement: document.querySelector(
        `#container_codeFams2_${numPage}`
      ),
      designation: document.querySelector(
        `#demande_appro_proposition_designation_${numPage}`
      ),
      fournisseur: document.querySelector(
        `#demande_appro_proposition_fournisseur_${numPage}`
      ),
      reference: document.querySelector(
        `#demande_appro_proposition_reference_${numPage}`
      ),
    };
  }

  /**
   * permet d'autocompleter la designation et la référence
   * @param {int} numPage
   */
  function autocompleteTheFieldsPage(numPage) {
    const { fournisseur, reference, designation } = recupInput(numPage);

    reset(fournisseur, reference, designation);

    autocompleteTheField(designation, "designation", numPage);
    autocompleteTheField(reference, "reference", numPage);
  }

  /**
   * permet d'effacer le contenu des champs fournisseur, référence, designation
   * @param {*} fournisseur
   * @param {*} reference
   * @param {*} designation
   */
  function reset(fournisseur, reference, designation) {
    if (fournisseur) fournisseur.value = "";
    if (reference) reference.value = "";
    if (designation) designation.value = "";
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
    const prototype = document.getElementById("child-prototype");
    if (prototype) prototype.remove();
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
