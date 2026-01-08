import { resetDropdown } from "../../utils/dropdownUtils";
import { updateDropdown } from "../../utils/selectionHandler";
import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
import { displayOverlay } from "../../utils/ui/overlay";
import { createFicheTechnique } from "./dalr";
import { changeTab } from "../utils/pageNavigation";

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
      // Si l'élément est "Prix Unitaire", on accepte les décimales avec un point
      if (selector.includes("proposition_PU_")) {
        // Remplacer tout sauf les chiffres et le point
        el.value = el.value.replace(/[^0-9.]/g, "");
        // S'assurer qu'il n'y a qu'un seul point
        el.value = el.value.replace(/\.{2,}/g, ".");
        // Si plusieurs points, on laisse uniquement le premier
        if (el.value.split(".").length > 2) {
          el.value = el.value.substring(0, el.value.lastIndexOf("."));
        }
      } else {
        // Pour les autres champs, on garde uniquement les chiffres
        el.value = el.value.replace(/[^\d]/g, "");
      }
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

      autocompleteTheField(el, type);
    });
  });

  // Champs "Famille"
  document.querySelectorAll('[id*="proposition_codeFams1_"]').forEach((el) => {
    const numPage = el.id.split("_").pop();

    initialiserFamilleEtSousFamille(numPage, el);
    gererChangementFamille(numPage, el);
  });

  // Champs "Sous-Famille"
  document.querySelectorAll('[id*="proposition_codeFams2_"]').forEach((el) => {
    const numPage = el.id.split("_").pop();
    const { fournisseur, reference, designation } = recupInput(numPage);

    el.addEventListener("change", () => {
      reset(fournisseur, reference, designation);
      autocompleteTheFieldsPage(numPage);
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

/**
 * permet d'autocompleter la designation et la référence
 * @param {int} numPage
 */
function autocompleteTheFieldsPage(numPage) {
  const { fournisseur, reference, designation, isCatalogueInput } =
    recupInput(numPage);
  let iscatalogue = isCatalogueInput.value;
  reset(fournisseur, reference, designation);
  console.log(iscatalogue == "");

  autocompleteTheField(designation, "designation", numPage, iscatalogue);
  autocompleteTheField(reference, "reference", numPage, iscatalogue);
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
    containerElement: document.querySelector(`#container_codeFams2_${numPage}`),
    designation: document.querySelector(
      `#demande_appro_proposition_designation_${numPage}`
    ),
    fournisseur: document.querySelector(
      `#demande_appro_proposition_fournisseur_${numPage}`
    ),
    reference: document.querySelector(
      `#demande_appro_proposition_reference_${numPage}`
    ),
    isCatalogueInput: document.querySelector(`#catalogue_${numPage}`),
  };
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
