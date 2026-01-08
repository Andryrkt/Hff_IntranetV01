import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
import { createFicheTechnique } from "./dalr";
import { changeTab } from "../utils/pageNavigation";
import { displayOverlay } from "../../utils/ui/overlay";

export function handleAllInputEvents() {
  // Utilitaire pour ajouter un listener à tous les éléments correspondant à un sélecteur
  const addInputListener = (selector, callback) => {
    document.querySelectorAll(selector).forEach((el) => {
      el.addEventListener("input", () => callback(el));
    });
  };

  // Champs numériques : Qté Dispo et Prix Unitaire
  const numericFields = [
    '[id*="proposition_qte_dispo_"]',
    '[id*="proposition_PU_"]',
  ];

  numericFields.forEach((selector) => {
    addInputListener(selector, (el) => {
      el.value = el.value.replace(/[^\d]/g, "");
    });
  });

  // Champs à mettre en majuscules + autocomplete
  const uppercaseWithAutocomplete = [
    { selector: '[id*="proposition_reference_"]', type: "reference" },
    { selector: '[id*="proposition_fournisseur_"]', type: "fournisseur" },
    {
      selector: '[id*="proposition_designation_"]',
      type: "designation",
      maxLen: 35,
    },
  ];

  uppercaseWithAutocomplete.forEach(({ selector, type, maxLen }) => {
    document.querySelectorAll(selector).forEach((el) => {
      el.addEventListener("input", () => {
        el.value = el.value.toUpperCase();
        if (maxLen) el.value = el.value.slice(0, maxLen);
      });

      if (type !== "designation") {
        autocompleteTheField(el, type);
      }
    });
  });
}

export function handleAllButtonEvents() {
  /******************************************
   * DEBUT BOUTON OK ET ENVOI DU FORMULAIRE *
   ******************************************/
  const boutonOK = document.getElementById("bouton_ok");
  const formValidation = document.querySelector(
    "form[name='da_proposition_validation']"
  );

  boutonOK.addEventListener("click", function (event) {
    event.preventDefault();
    let allPrixUnitaire = document.querySelectorAll(
      '[id^="demande_appro_proposition_PU_"]'
    ); // tous les prix unitaires
    let filteredPrixUnitaire = Array.from(allPrixUnitaire).filter(
      (el) => el.dataset.catalogue === "0"
    ); // tous les prix unitaires des pages d'articles non catalogués
    console.log(filteredPrixUnitaire);

    let bloquer = filteredPrixUnitaire.some((e) => {
      let page = e.id.split("_").pop();
      let tableBody = document.getElementById(`tableBody_${page}`);
      if (!tableBody) {
        return e.value.trim() === "" && tableBody.children.length == 0;
      }
    });
    if (bloquer) {
      alert(
        "Votre demande est bloquée parce que vous devez d'abord renseigner tous les champs PU des articles non catalogué."
      );
    } else {
      const selectedValues = localStorage.getItem("selectedValues");
      console.log(selectedValues);
      document.getElementById("da_proposition_validation_refsValide").value =
        selectedValues;

      formValidation.submit(); // soumettre le formulaire de validation
    }
  });
  /****************************************
   * FIN BOUTON OK ET ENVOI DU FORMULAIRE *
   ****************************************/

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
  // Tous les boutons add-file (joindre une fiche technique)
  document.querySelectorAll(".add-file").forEach((addFile) => {
    addFile.addEventListener("click", function () {
      const nbrLine = addFile.dataset.nbrLine;
      const numLigneTableau = addFile.dataset.nbrLineTable;
      const inputFile = document.getElementById(
        `demande_appro_lr_collection_DALR_${nbrLine}${numLigneTableau}_nomFicheTechnique`
      );
      createFicheTechnique(nbrLine, numLigneTableau, inputFile);
    });
  });
}
