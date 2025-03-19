import { updateDropdown } from '../../utils/selectionHandler';

export function eventOnFamille() {
  document.querySelectorAll('[id*="artFams1"]').forEach((famille) => {
    let familleId = famille.id;
    let sousFamilleId = familleId.replace('artFams1', 'artFams2');
    let baseId = sousFamilleId.replace('demande_appro_form_DAL', '');
    let spinnerId = `spinner${baseId}`;
    let containerId = `container${baseId}`;
    let sousFamille = document.getElementById(sousFamilleId);
    let spinnerElement = document.getElementById(spinnerId);
    let containerElement = document.getElementById(containerId);
    console.log(famille);

    famille.addEventListener('change', function () {
      console.log(famille);

      if (famille.value !== '') {
        console.log(famille.value);

        updateDropdown(
          sousFamille,
          `api/demande-appro/sous-famille/${famille.value}`,
          '-- Choisir une sous-famille --',
          spinnerElement,
          containerElement
        );
      }
    });
  });
}
