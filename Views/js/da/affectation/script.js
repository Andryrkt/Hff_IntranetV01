import { AutoComplete } from "../../utils/AutoComplete";
import { displayOverlay } from "../../utils/ui/overlay";
import { getAllFournisseurs, getAllReferences } from "../data/fetchData";

document.addEventListener("DOMContentLoaded", async function () {
  const { data } = await getAllReferences();
  const referencesMap = new Map(data.map((ref) => [ref.reference, ref]));

  setupInputFormatters();
  setupReferenceValidation(referencesMap);
  setupAutocompleteField();
  confirmForm();
});

function setupInputFormatters() {
  setupInputFormatter(".da-art-refp", 35);
  setupInputFormatter(".da-art-desi", 35);
  setupInputFormatter(".da-nom-frn", 50);
}

function setupInputFormatter(selector, maxLength) {
  document.querySelectorAll(selector).forEach((input) => {
    input.addEventListener("input", function () {
      this.value = this.value.toUpperCase().slice(0, maxLength);
    });
  });
}

function setupReferenceValidation(referencesMap) {
  document.querySelectorAll(".da-art-refp").forEach((refp) => {
    refp.addEventListener("blur", async function () {
      await handleReferenceBlur(refp, referencesMap);
    });
  });
}

async function handleReferenceBlur(refp, referencesMap) {
  const refpValue = refp.value.trim();
  if (!refpValue) return;

  const articleFound = referencesMap.get(refpValue);
  const fields = getRelatedFields(refp);

  if (!articleFound) {
    await showReferenceNotFoundError();
    resetArticleFields({ ...fields, refp });
    refp.focus();
  } else {
    populateArticleFields(articleFound, fields);
  }
}

function getRelatedFields(refp) {
  return {
    articleStocke: getInputLine(refp, '[id$="_articleStocke"]'),
    desi: getInputLine(refp, '[id$="_artDesi"]'),
    constp: getInputLine(refp, '[id$="_artConstp"]'),
    prix: getInputLine(refp, '[id$="_prixUnitaire"]'),
    numFrn: getInputLine(refp, '[id$="_numeroFournisseur"]'),
    nomFrn: getInputLine(refp, '[id$="_nomFournisseur"]'),
  };
}

function getInputLine(el, selector) {
  return el.parentElement.parentElement.querySelector(selector);
}

async function showReferenceNotFoundError() {
  await Swal.fire({
    icon: "error",
    title: "Référence inexistante",
    html: "La référence saisie n'existe pas pour la liste de constructeurs </br> (<b>'ALI', 'BOI', 'CEN', 'FBU', 'HAB', 'OUT', 'ZDI', 'INF', 'MIN'</b>)</br> Veuillez en saisir une dans la liste s'il vous plaît.",
  });
}

function resetArticleFields(fields) {
  fields.articleStocke.checked = false;
  fields.refp.value = "";
  fields.desi.value = "";
  fields.nomFrn.value = "";
  fields.prix.value = "0";
  fields.numFrn.value = "-";
  fields.constp.value = "-";
  fields.desi.classList.remove("non-modifiable");
  fields.nomFrn.classList.remove("non-modifiable");
}

function populateArticleFields(articleFound, fields) {
  fields.articleStocke.checked = false;

  if (articleFound.constp === "ZDI") {
    fields.constp.value = "ZDI";
    fields.prix.value = "0";
    fields.desi.classList.remove("non-modifiable");
    fields.nomFrn.classList.remove("non-modifiable");
  } else {
    fields.articleStocke.checked = true;
    fields.constp.value = articleFound.constp;
    fields.desi.value = articleFound.desi;
    fields.nomFrn.value = articleFound.nom_frn;
    fields.prix.value = articleFound.prix_unitaire;
    fields.numFrn.value = articleFound.num_frn;
    fields.desi.classList.add("non-modifiable");
    fields.nomFrn.classList.add("non-modifiable");
  }
}

function setupAutocompleteField() {
  document.querySelectorAll(".da-nom-frn").forEach((field) => {
    let numeroFournisseur = getInputLine(field, '[id$="_numeroFournisseur"]');
    let suggestionContainer = field.nextElementSibling;
    let loaderElement = suggestionContainer.nextElementSibling;

    new AutoComplete({
      inputElement: field,
      suggestionContainer: suggestionContainer,
      loaderElement: loaderElement,
      debounceDelay: 300,
      fetchDataCallback: async () => {
        const cache = JSON.parse(
          localStorage.getItem("autocompleteCache") || "{}"
        );

        if (!cache.fournisseurs) {
          const data = await getAllFournisseurs(); // fetch si cache vide
          cache.fournisseurs = data;
          console.log("préchargement fournisseurs OK");
          localStorage.setItem("autocompleteCache", JSON.stringify(cache));
          return data;
        }

        return cache.fournisseurs;
      },
      displayItemCallback: (item) =>
        `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`,
      itemToStringCallback: (item) => `- ${item.nomfournisseur}`,
      onSelectCallback: (item) => {
        field.value = item.nomfournisseur;
        numeroFournisseur.value = item.numerofournisseur;
      },
      itemToStringForBlur: (item) => item.nomfournisseur,
      onBlurCallback: (found) => {
        if (!found && field.value.trim() !== "") {
          Swal.fire({
            icon: "warning",
            title: "Attention ! Fournisseur non trouvé !",
            html: `Le fournisseur saisi n'existe pas, veuillez en sélectionner un dans la liste. Ou laisser vide car ce champ n'est pas obligatoire.`,
            confirmButtonText: "OK",
            customClass: {
              htmlContainer: "swal-text-left",
            },
          }).then(() => {
            field.focus();
            field.value = "";
            numeroFournisseur.value = "-";
          });
        }
      },
    });
  });
}

function confirmForm() {
  const form = document.querySelector('form[name="da_affectation"]');
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    Swal.fire({
      icon: "warning",
      title: "Attention !",
      html: `Voulez-vous vraiment enregistrer les affectations sur les lignes d'articles ?`,
      showCancelButton: true,
      confirmButtonText: "Oui, enregistrer",
      cancelButtonText: "Non, annuler",
      customClass: {
        htmlContainer: "swal-text-left",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, "Enregistrement en cours ...");
        form.submit();
      }
    });
  });
}
