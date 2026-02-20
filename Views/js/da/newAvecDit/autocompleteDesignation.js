import { FetchManager } from "../../api/FetchManager";
import { AutoComplete } from "../../utils/AutoComplete";
import { updateDropdown } from "../../utils/selectionHandler";

export function initializeAutoCompletionDesi(designation) {
  let baseId = designation.id.replace("demande_appro_form_DAL", "");

  let fields = {
    famille: getFieldByGeneratedId(designation.id, "codeFams1"),
    sousFamille: getFieldByGeneratedId(designation.id, "codeFams2"),
    familleLibelle: getFieldByGeneratedId(designation.id, "artFams1"),
    sousFamilleLibelle: getFieldByGeneratedId(designation.id, "artFams2"),
  };

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

  if (fields.famille && fields.sousFamille) {
    new AutoComplete({
      inputElement: designation,
      suggestionContainer: suggestionContainer,
      loaderElement: loaderElement,
      debounceDelay: 150,
      fetchDataCallback: () =>
        fetchDesignations(fields.famille, fields.sousFamille),
      displayItemCallback: (item) =>
        `Référence: ${item.referencepiece} - Fournisseur: ${item.fournisseur} - Prix: ${item.prix} <br>Désignation: ${item.designation}`,
      itemToStringCallback: (item) =>
        `${item.referencepiece} - ${item.designation}`,
      itemToStringForBlur: (item) => `${item.designation}`,
      onBlurCallback: (found) => onBlurEvent(found, designation, fields),
      onSelectCallback: (item) =>
        handleValueOfTheFields(item, designation, fields),
    });
  } else {
    console.error("Certains éléments nécessaires sont manquants.");
  }
}

async function fetchDesignations(famille, sousFamille) {
  const fetchManager = new FetchManager();
  let codeFamille = famille.value !== "" ? famille.value : "-";
  let codeSousFamille = sousFamille.value !== "" ? sousFamille.value : "-";

  return await fetchManager.get(
    `api/demande-appro/autocomplete/all-designation-zst/${codeFamille}/${codeSousFamille}`
  );
}

function getFieldByGeneratedId(baseId, suffix) {
  return document.getElementById(baseId.replace("artDesi", suffix));
}

async function handleValueOfTheFields(item, designation, fields) {
  console.log(item);
  let referencePiece = getFieldByGeneratedId(designation.id, "artRefp");
  let numeroFournisseur = getFieldByGeneratedId(
    designation.id,
    "numeroFournisseur"
  );
  let nomFournisseur = getFieldByGeneratedId(designation.id, "nomFournisseur");
  let famille = fields.famille;
  let sousFamille = fields.sousFamille;
  let familleLibelle = fields.familleLibelle;
  let sousFamilleLibelle = fields.sousFamilleLibelle;

  if (famille.value !== item.codefamille) {
    famille.value = item.codefamille;
    familleLibelle.value = famille.options[famille.selectedIndex].text;
    await changeSousFamille(famille, sousFamille);
  } else if (sousFamille.value !== item.codesousfamille) {
    await changeSousFamille(famille, sousFamille);
  }
  sousFamille.value = item.codesousfamille;
  sousFamilleLibelle.value =
    sousFamille.options[sousFamille.selectedIndex].text;
  designation.value = item.designation;
  referencePiece.value = item.referencepiece;
  numeroFournisseur.value = item.numerofournisseur;
  nomFournisseur.value = item.fournisseur;
  famille.classList.add("non-modifiable");
  sousFamille.classList.add("non-modifiable");
  designation.classList.remove("champ-surligne-jaune");
  designation.classList.add("non-modifiable");
  nomFournisseur.classList.add("non-modifiable");
}

async function changeSousFamille(famille, sousFamille) {
  let baseId = sousFamille.id.replace("demande_appro_form_DAL", "");

  try {
    await updateDropdown(
      sousFamille,
      `api/demande-appro/sous-famille/${famille.value}`,
      "-- Choisir une sous-famille --",
      document.getElementById(`spinner${baseId}`),
      document.getElementById(`container${baseId}`)
    );
  } catch (error) {
    console.error("Erreur dans changeSousFamille:", error);
  } finally {
    console.log("Fin de changeSousFamille");
  }
}

function onBlurEvent(found, designation, fields) {
  if (designation.value.trim() !== "") {
    let baseId = designation.id.replace("artDesi", "");
    let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
    let nomFournisseur = getFieldByGeneratedId(
      designation.id,
      "nomFournisseur"
    );
    let numeroFournisseur = getFieldByGeneratedId(
      designation.id,
      "numeroFournisseur"
    );
    let referencePiece = getFieldByGeneratedId(designation.id, "artRefp");
    let oldValueFamille = fields.famille.value;
    let oldValueSousFamille = fields.sousFamille.value;

    // Texte rouge ou non, ajout de valeur dans catalogue
    allFields.forEach((field) => {
      if (found) {
        field.classList.remove("text-danger");
      } else {
        field.classList.add("text-danger");
      }
      if (field.id.includes("catalogue")) {
        field.checked = found;
      }
    });

    // Si non trouvé alors valeur de reférence pièce = 'ST'
    referencePiece.value = found ? referencePiece.value : "ST";

    // Champ readonly pour le nom de fournisseur
    nomFournisseur.readOnly = found;
    if (!found) {
      numeroFournisseur.value = "99"; // valeur par défaut du numéro de fournisseur
      nomFournisseur.value = "CHANGER DE FOURNISSEUR"; // valeur par défaut du nom de fournisseur (correspondant au numéro '99')
    }

    // Champs requis ou non et changement de valeur de champs (famille et sous-famille seulement)
    Object.values(fields).forEach((field) => {
      field.required = found;
      field.value = found ? field.value : "";
    });

    // réinitialiser l'autocomplete de désignation
    if (
      !found &&
      oldValueFamille !== fields.famille.value &&
      oldValueSousFamille !== fields.sousFamille.value
    ) {
      initializeAutoCompletionDesi(designation);
    }
  }
}
