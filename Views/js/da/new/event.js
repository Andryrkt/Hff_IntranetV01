import { resetDropdown } from '../../utils/dropdownUtils';
import { updateDropdown } from '../../utils/selectionHandler';
import { autocompleteTheFields } from './autocompletion';

export function eventOnFamille() {
  let familles = document.querySelectorAll(
    '[id*="codeFams1"][id*="form_DAL"]:not([id*="__name__"])'
  ); // éléments avec id contenant "fams1" et "form_DAL" mais ne contenant pas "__name__"

  familles.forEach((famille) => {
    let sousFamille = getField(famille.id, 'codeFams1', 'codeFams2');
    let familleLibelle = getField(famille.id, 'codeFams1', 'artFams1');
    let sousFamilleLibelle = getField(famille.id, 'codeFams1', 'artFams2');
    let spinnerElement = getField(sousFamille.id,'demande_appro_form_DAL','','spinner');
    let containerElement = getField(sousFamille.id,'demande_appro_form_DAL','','container');

    famille.addEventListener('change', function () {
      if (famille.value !== '') {
        updateDropdown(
          sousFamille,
          `api/demande-appro/sous-famille/${famille.value}`,
          '-- Choisir une sous-famille --',
          spinnerElement,
          containerElement
        );
      } else {
        resetDropdown(sousFamille, '-- Choisir une sous-famille --');
      }
      sousFamille.value = '';
      familleLibelle.value =
        this.selectedIndex === 0 ? '' : this.options[this.selectedIndex].text;
      handleDesignation(famille.id);
    });
    sousFamille.addEventListener('change', function () {
      sousFamilleLibelle.value =
        this.selectedIndex === 0 ? '' : this.options[this.selectedIndex].text;
      handleDesignation(famille.id);
    });
  });
}

function handleDesignation(familleId) {
  document.querySelector(
    `#${familleId.replace('codeFams1', 'artDesi')}`
  ).value = '';
  autocompleteTheFields();
}

function getField(oldId, idSearch, idReplace, suffixId = '') {
  return document.getElementById(
    `${suffixId}${oldId.replace(idSearch, idReplace)}`
  );
}
