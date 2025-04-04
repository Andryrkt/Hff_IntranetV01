import { resetDropdown } from '../../utils/dropdownUtils';
import { updateDropdown } from '../../utils/selectionHandler';
import { autocompleteTheFields } from './autocompletion';

export function eventOnFamille() {
  let familles = document.querySelectorAll(
    '[id*="codeFams1"][id*="form_DAL"]:not([id*="__name__"])'
  ); // éléments avec id contenant "fams1" et "form_DAL" mais ne contenant pas "__name__"

  familles.forEach((famille) => {
    let familleId = famille.id;
    let sousFamilleId = familleId.replace('codeFams1', 'codeFams2');
    let familleLibelleId = familleId.replace('codeFams1', 'artFams1');
    let sousFamilleLibelleId = familleId.replace('codeFams1', 'artFams2');
    let baseId = sousFamilleId.replace('demande_appro_form_DAL', '');
    let spinnerId = `spinner${baseId}`;
    let containerId = `container${baseId}`;
    let familleLibelle = document.getElementById(familleLibelleId);
    let sousFamilleLibelle = document.getElementById(sousFamilleLibelleId);
    let sousFamille = document.getElementById(sousFamilleId);
    let spinnerElement = document.getElementById(spinnerId);
    let containerElement = document.getElementById(containerId);

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
      familleLibelle.value = this.options[this.selectedIndex].text;
      handleDesignation(familleId);
    });
    sousFamille.addEventListener('change', function () {
      sousFamilleLibelle.value = this.options[this.selectedIndex].text;
      handleDesignation(familleId);
    });
  });
}

function handleDesignation(familleId) {
  document.querySelector(
    `#${familleId.replace('codeFams1', 'artDesi')}`
  ).value = '';
  autocompleteTheFields();
}
