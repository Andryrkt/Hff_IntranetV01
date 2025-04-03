import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';
import { updateDropdown } from '../../utils/selectionHandler';
import { getTheField } from './field';

export function autocompleteTheFields(line) {
  let designation = getTheField(line, 'artDesi');

  initializeAutoCompleteForDesignation(designation);
}

function initializeAutoCompleteForDesignation(designation) {
  let baseId = designation.id.replace('demande_appro_form_DAL', '');

  let fields = {
    famille: getFieldByGeneratedId(designation.id, 'codeFams1'),
    sousFamille: getFieldByGeneratedId(designation.id, 'codeFams2'),
    familleLibelle: getFieldByGeneratedId(designation.id, 'artFams1'),
    sousFamilleLibelle: getFieldByGeneratedId(designation.id, 'artFams2'),
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
      displayItemCallback: displayDesignation,
      itemToStringCallback: (item) =>
        `${item.fournisseur} - ${item.designation}`,
      itemToStringForBlur: (item) => `${item.designation}`,
      onBlurCallback: (found) => onBlurEvent(found, designation, fields),
      onSelectCallback: (item) =>
        handleValueOfTheFields(item, designation, fields),
    });
  } else {
    console.error('Certains éléments nécessaires sont manquants.');
  }
}

async function fetchDesignations(famille, sousFamille) {
  const fetchManager = new FetchManager();
  let codeFamille = famille.value !== '' ? famille.value : '-';
  let codeSousFamille = sousFamille.value !== '' ? sousFamille.value : '-';

  return await fetchManager.get(
    `demande-appro/autocomplete/all-designation/${codeFamille}/${codeSousFamille}`
  );
}

function displayDesignation(item) {
  return `Fournisseur: ${item.fournisseur} - Désignation: ${item.designation} - Prix: ${item.prix}`;
}

function getFieldByGeneratedId(baseId, suffix) {
  return document.getElementById(baseId.replace('artDesi', suffix));
}

async function handleValueOfTheFields(item, designation, fields) {
  let referencePiece = document.getElementById(
    designation.id.replace('artDesi', 'artRefp')
  );
  let numeroFournisseur = document.getElementById(
    designation.id.replace('artDesi', 'numeroFournisseur')
  );
  let nomFournisseur = document.getElementById(
    designation.id.replace('artDesi', 'nomFournisseur')
  );
  let famille = fields.famille;
  let sousFamille = fields.sousFamille;
  let familleLibelle = fields.familleLibelle;
  let sousFamilleLibelle = fields.sousFamilleLibelle;
  console.log(item);

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
}

async function changeSousFamille(famille, sousFamille) {
  let baseId = sousFamille.id.replace('demande_appro_form_DAL', '');

  try {
    await updateDropdown(
      sousFamille,
      `api/demande-appro/sous-famille/${famille.value}`,
      '-- Choisir une sous-famille --',
      document.getElementById(`spinner${baseId}`),
      document.getElementById(`container${baseId}`)
    );
  } catch (error) {
    console.error('Erreur dans changeSousFamille:', error);
  } finally {
    console.log('Fin de changeSousFamille');
  }
}

function onBlurEvent(found, designation, fields) {
  if (designation.value.trim() !== '') {
    let baseId = designation.id.replace('artDesi', '');
    let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
    let nomFournisseur = getFieldByGeneratedId(
      designation.id,
      'nomFournisseur'
    );

    // Champs requis ou non et valeurs
    Object.values(fields).forEach((field) => {
      field.required = found;
      field.value = found ? field.value : '';
    });

    // réinitialiser l'autocomplete de désignation
    if (!found) {
      initializeAutoCompleteForDesignation(designation);
    }

    // Champ readonly ou non
    nomFournisseur.readOnly = found;

    allFields.forEach((field) => {
      if (found) {
        field.classList.remove('text-danger');
      } else {
        field.classList.add('text-danger');
      }
      if (field.id.includes('catalogue')) {
        field.checked = found;
      }
    });
  }
}
