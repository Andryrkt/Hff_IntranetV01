import { ajouterReference } from "./article";
import { autocompleteTheField } from "./autocompletion";
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

export function handleFormSubmit() {
  const actionsConfig = {
    brouillon: {
      title: "Confirmer l’enregistrement",
      html: `Souhaitez-vous enregistrer <strong class="text-primary">provisoirement</strong> cette demande ?<br><small class="text-primary"><strong><u>NB</u>: </strong>Elle ne sera pas transmise au service émetteur.</small>`,
      icon: "question",
      confirmButtonText: "Oui, Enregistrer",
      canceledText: "L’enregistrement provisoire a été annulé.",
    },
    enregistrer: {
      title: "Confirmer proposition(s)",
      html: `Êtes-vous sûr de vouloir <strong style="color: #f8bb86;">envoyer la/les proposition(s)</strong> ?<br><small style="color: #f8bb86;"><strong><u>NB</u>: </strong>Elle sera transmise au service émetteur pour validation.</small>`,
      icon: "warning",
      confirmButtonText: "Oui, Envoyer proposition(s)",
      canceledText: "L’envoi de la/les proposition(s) a été annulée.",
    },
    valider: {
      title: "Confirmer la validation",
      html: `Êtes-vous sûr de vouloir <strong class="text-success"">valider</strong> cette demande ?<br><small class="text-success""><strong><u>NB</u>: </strong>Après validation de la demande, le statut de la Da sera <strong class="text-success">'Bon d’achats validé'</strong>.</small>`,
      icon: "warning",
      confirmButtonText: "Oui, Valider",
      canceledText: "La validation de la demande a été annulée.",
    },
  };

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault(); // empêcher l'envoi immédiat
    const action = e.submitter.name; // 👉 nom (attribut "name") du bouton qui a déclenché le submit

    const config = actionsConfig[action];
    if (!config) return;

    if (action !== "brouillon" && blockFournisseur99(action)) return;

    Swal.fire({
      title: config.title,
      html: config.html,
      icon: config.icon,
      showCancelButton: true,
      reverseButtons: true,
      confirmButtonColor: "#198754",
      cancelButtonColor: "#6c757d",
      confirmButtonText: config.confirmButtonText,
      cancelButtonText: "Non, Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true);
        document.getElementById("child-prototype").remove();

        // ajouter un champ caché avec l’action choisie
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = action;
        hidden.value = "1";
        document.getElementById("myForm").appendChild(hidden);

        document.getElementById("myForm").submit(); // n’émule pas le clic sur le bouton d’envoi → donc le name et value du bouton cliqué ne sont pas envoyés.
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        // ❌ Si l'utilisateur annule
        Swal.fire({
          icon: "info",
          title: "Annulé",
          text: config.canceledText,
          timer: 2000,
          showConfirmButton: false,
        });
      }
    });
  });
}

function blockFournisseur99(action) {
  let pageAvecFRN99 = [];
  const messageErreur = {
    enregistrer: "L'envoi de la proposition au sevice émetteur est bloquée.",
    valider: "La validation de la demande d'approvissionement est bloquée.",
  };
  const numeroDa = document
    .querySelector(".tab-pane.fade.show.active.dalr")
    .id.split("_")
    .pop();
  const numLignes = JSON.parse(localStorage.getItem(`idTabs_${numeroDa}`));

  numLignes.forEach((numLigne) => {
    let tBody = document.querySelector(`#tableBody_${numLigne}`);
    let selectedRow = tBody.querySelector("tr.table-active");
    // un DALR a été choisi sur la table
    if (selectedRow) {
      let numeroFournisseur = selectedRow.querySelector(
        "td.numero-fournisseur"
      ).textContent;

      if (numeroFournisseur === "99")
        pageAvecFRN99.push(numLignes.indexOf(numLigne) + 1); // numéro de la page
    } else {
      let numeroFournisseur = document.querySelector(
        `#numeroFournisseur_${numLigne}`
      ).value;

      if (numeroFournisseur === "99")
        pageAvecFRN99.push(numLignes.indexOf(numLigne) + 1); // numéro de la page
    }
  });

  console.log(pageAvecFRN99);

  if (pageAvecFRN99.length > 0) {
    let raison =
      'Parmi les articles proposés et choisis, le fournisseur est "99" sur quelque(s) page(s).';
    let solution =
      'Veuillez ajouter ou choisir une article avec un fournisseur autre que "99" sur les pages concernées.';
    let pageConcernee = "<ul>";
    pageAvecFRN99.forEach((page) => {
      pageConcernee += `<li>Page n° <b>${page}</b></li>`;
    });
    pageConcernee += "</ul>";
    Swal.fire({
      icon: "error",
      title: "Echec de l'opération",
      html: `${messageErreur[action]} <br> <b> <u>Raison</u> : </b> ${raison} <br> <b> <u>Solution</u> : </b> ${solution} <br><b> <u>Page(s) concernée(s)</u> : </b> ${pageConcernee}`,
      background: "#f8d7da",
      color: "#842029",
      iconColor: "#dc3545",
      confirmButtonColor: "#dc3545",
      customClass: {
        htmlContainer: "swal-text-left",
      },
    });
  }

  return pageAvecFRN99.length > 0;
}
