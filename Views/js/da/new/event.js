import { resetDropdown } from '../../utils/dropdownUtils';
import { updateDropdown } from '../../utils/selectionHandler';
import { autocompleteTheFields } from './dal';
import { getTheField } from './field';

export function eventOnFamille(line) {
  let famille = getTheField(line, 'codeFams1'); // famille correspondant à la ligne line
  let sousFamille = getTheField(line, 'codeFams2'); // sous-famille correspondant à la ligne line
  let familleLibelle = getTheField(line, 'artFams1'); //  libelle de la famille correspondant à la ligne line
  let sousFamilleLibelle = getTheField(line, 'artFams2'); // libelle de la sous-famille correspondant à la ligne line
  let spinnerElement = getTheField(line, 'codeFams2', 'spinner');
  let containerElement = getTheField(line, 'codeFams2', 'container');

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
    handleDesignation(famille.id, line);
  });
  sousFamille.addEventListener('change', function () {
    sousFamilleLibelle.value =
      this.selectedIndex === 0 ? '' : this.options[this.selectedIndex].text;
    handleDesignation(famille.id, line);
  });
}

function handleDesignation(familleId, line) {
  document.querySelector(
    `#${familleId.replace('codeFams1', 'artDesi')}`
  ).value = '';
  autocompleteTheFields(line);
}
