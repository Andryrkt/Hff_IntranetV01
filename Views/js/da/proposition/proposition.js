import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
import { changeTab, initialiserIdTabs, showTab } from "./pageNavigation";
import { updateDropdown } from "../../utils/selectionHandler";
import { boutonRadio } from "./boutonRadio";
import { createFicheTechnique, handleRowClick } from "./dalr";

document.addEventListener("DOMContentLoaded", function () {
  initialiserIdTabs(); // initialiser les ID des onglets pour la navigation
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

      // filteredPrixUnitaire.forEach((prixUnitaireInput) => {
      //   let line = prixUnitaireInput.id.split("_").pop();
      //   params.append(`PU[${line}]`, prixUnitaireInput.value);
      // });
      // let url = this.getAttribute("href");
      // // vérifier s'il y a au moins un paramètre
      // if (params.toString()) {
      //   url += `?${params.toString()}`;
      // }

      formValidation.submit(); // soumettre le formulaire de validation
    }
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
      isCatalogueInput: document.querySelector(`#catalogue_${numPage}`),
    };
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

  document.getElementById("myForm").addEventListener("submit", function (e) {
    const prototype = document.getElementById("child-prototype");
    if (prototype) prototype.remove();
  });

  /**=============================================
   * Desactive le bouton OK si la cage à cocher n'est pas cocher
   *==============================================*/
  const cageACocherInput = document.querySelector(
    "#demande_appro_lr_collection_estValidee"
  );
  const boutonOkInput = document.querySelector("#bouton_ok");

  // Fonction pour activer ou désactiver le bouton
  function verifierCaseCochee() {
    if (cageACocherInput.checked) {
      boutonOkInput.classList.remove("d-none");
    } else {
      boutonOkInput.classList.add("d-none");
    }
  }

  // Initialiser l'état du bouton au chargement
  verifierCaseCochee();

  // Écouteur d'événement sur la case à cocher
  cageACocherInput.addEventListener("change", verifierCaseCochee);

  /**=================================================================
   * lorsqu'on clique sur le bouton radio et envoyer le  proposition
   *==================================================================*/
  boutonRadio();

  /**===========================================
   * EVENEMENT SUR LES LIGNES DU TABLEAU
   *============================================*/
  document.querySelectorAll('tr[role="button"]').forEach((row) => {
    row.addEventListener("click", handleRowClick);
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);

  const conversationContainer = document.getElementById(
    "conversationContainer"
  );

  if (!conversationContainer) return;

  const interval = setInterval(() => {
    const firstChild = conversationContainer.firstElementChild;

    if (firstChild && firstChild.offsetHeight > 0) {
      // Le contenu est prêt, on peut scroller en bas
      conversationContainer.scrollTop = conversationContainer.scrollHeight;

      // Stoppe le setInterval
      clearInterval(interval);
    }
  }, 100);
});
