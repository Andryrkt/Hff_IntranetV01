export function createFieldAndAppendTo(
  classe,
  prototype,
  fieldName,
  parentField
) {
  let field = document.createElement('div');
  field.classList.add(classe);
  field.appendChild(prototype.querySelector(`[id*="${fieldName}"]`));
  parentField.appendChild(field);
}

export function createFams2AndAppendTo(className, prototype, parentField) {
  // Création du conteneur principal
  let field = document.createElement('div');
  field.classList.add(className);

  // Sélection de l'élément cible
  let fams2Field = prototype.querySelector(`[id*="artFams2"]`);

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = fams2Field.id.replace('demande_appro_form_DAL', '');
  let spinnerId = `spinner${baseId}`;
  let containerId = `container${baseId}`;

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement('div');
  spinnerContainer.classList.add('spinner-container');
  spinnerContainer.innerHTML = `
        <div class="spinner" id="${spinnerId}" style="display: none;">
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
