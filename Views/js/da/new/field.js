import { resetDropdown } from '../../utils/dropdownUtils';

export function createFieldAndAppendTo(
  classe,
  prototype,
  fieldName,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement('div');
  fieldContainer.classList.add(classe);

  // Champ à mettre dans le conteneur
  let field = prototype.querySelector(`[id*="${fieldName}"]`);
  let dateFinSouhaitee = document.getElementById(
    'demande_appro_form_dateFinSouhaite'
  ).value;
  field.required = !['commentaire', 'catalogue'].includes(fieldName);
  field.value =
    fieldName === 'dateFinSouhaite'
      ? dateFinSouhaitee
      : fieldName === 'artConstp'
      ? 'ZST'
      : field.value;

  // Append the field
  fieldContainer.appendChild(field);
  parentField.appendChild(fieldContainer);
}

export function createRemoveButtonAndAppendTo(prototype, parentField) {
  // Création du conteneur principal
  let fieldContainer = document.createElement('div');
  fieldContainer.classList.add('w-2');

  // Bouton supprimer
  let removeButton = document.createElement('span');
  removeButton.title = 'Supprimer la ligne de DA';
  removeButton.style.cursor = 'pointer';
  removeButton.innerHTML = '<i class="fas fa-times fs-4"></i>';
  removeButton.addEventListener('click', function () {
    document.getElementById(prototype.id).remove();
  });

  // Append the field
  fieldContainer.appendChild(removeButton);
  parentField.appendChild(fieldContainer);
}

export function createFams2AndAppendTo(className, prototype, parentField) {
  // Création du conteneur principal
  let field = document.createElement('div');
  field.classList.add(className);

  // Sélection de l'élément cible
  let fams2Field = prototype.querySelector(`[id*="codeFams2"]`);

  // Effacer tous les options
  resetDropdown(fams2Field, '-- Choisir une sous-famille --');

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = fams2Field.id.replace('demande_appro_form_DAL', '');
  let spinnerId = `spinner${baseId}`;
  let containerId = `container${baseId}`;

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement('div');
  spinnerContainer.classList.add('spinner-container');
  spinnerContainer.innerHTML = `
        <div class="spinner-load m-auto" id="${spinnerId}" style="display: none;">
            ${'<div></div>'.repeat(12)} 
        </div>
    `;

  // Création du conteneur de l'élément cible
  let containerDiv = document.createElement('div');
  containerDiv.id = containerId;
  containerDiv.appendChild(fams2Field);

  // Ajout des éléments au conteneur principal
  field.append(spinnerContainer, containerDiv);

  // Ajout du conteneur principal au parent
  parentField.appendChild(field);
}

export function createDesiAndAppendTo(className, prototype, parentField) {
  // Création du conteneur principal
  let field = document.createElement('div');
  field.classList.add(className);

  // Sélection de l'élément cible
  let DesiField = prototype.querySelector(`[id*="artDesi"]`);

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = DesiField.id.replace('demande_appro_form_DAL', '');
  let spinnerId = `spinner${baseId}`;
  let suggestionId = `suggestion${baseId}`;

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement('div');
  spinnerContainer.id = spinnerId;
  spinnerContainer.classList.add('spinner');
  spinnerContainer.style.display = 'none';

  // Création du conteneur de l'élément cible
  let containerDiv = document.createElement('div');
  containerDiv.id = suggestionId;
  containerDiv.classList.add('suggestions-container');

  // Ajout des éléments au conteneur principal
  field.append(DesiField, containerDiv, spinnerContainer);

  // Ajout du conteneur principal au parent
  parentField.appendChild(field);
}

export function formatAllField() {
  let designations = document.querySelectorAll(`[id*="artDesi"]`);
  let quantites = document.querySelectorAll(`[id*="qteDem"]`);
  designations.forEach((designation) => {
    designation.addEventListener('input', function () {
      designation.value = designation.value.toUpperCase();
    });
  });
  quantites.forEach((quantite) => {
    quantite.addEventListener('input', function () {
      quantite.value = quantite.value.replace(/[^\d]/g, '');
    });
  });
}
