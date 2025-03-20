import { resetDropdown } from '../../utils/dropdownUtils';
import { updateDropdown } from '../../utils/selectionHandler';
import { autocompleteTheFields } from './autocompletion';

export function eventOnFamille() {
  let familles = document.querySelectorAll(
    '[id*="artFams1"][id*="form_DAL"]:not([id*="__name__"])'
  ); // éléments avec id contenant "artFams1" et "form_DAL" mais ne contenant pas "__name__"

  familles.forEach((famille) => {
    let familleId = famille.id;
    let sousFamilleId = familleId.replace('artFams1', 'artFams2');
    let baseId = sousFamilleId.replace('demande_appro_form_DAL', '');
    let spinnerId = `spinner${baseId}`;
    let containerId = `container${baseId}`;
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
      document.querySelector(
        `#${familleId.replace('artFams1', 'artDesi')}`
      ).value = '';
      autocompleteTheFields();
    });
    sousFamille.addEventListener('change', autocompleteTheFields);
  });
}
