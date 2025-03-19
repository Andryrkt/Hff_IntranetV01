let container = document.getElementById('children-container');

export function ajouterUneLigne() {
  let newIndex = parseInt(localStorage.getItem('index')) + 1; // Déterminer un index unique pour les nouveaux champs
  localStorage.setItem('index', newIndex); // Changer la valeur de newIndex
  let prototype = document
    .getElementById('child-prototype')
    .firstElementChild.cloneNode(true); // Clonage du prototype

  // Mettre à jour dynamiquement les IDs et Names
  prototype.id = replaceNameToNewIndex(prototype.id, newIndex);
  prototype.querySelectorAll('[id], [name]').forEach(function (element) {
    element.id = element.id
      ? replaceNameToNewIndex(element.id, newIndex)
      : element.id;
    element.name = element.name
      ? replaceNameToNewIndex(element.name, newIndex)
      : element.name;
  });

  // Créer la structure Bootstrap "row g-3"
  let row = document.createElement('div');
  row.classList.add('row', 'g-3');

  let fields = [
    ['w-15', 'artFams1'],
    ['w-20', 'artFams2'],
    ['w-25', 'artDesi'],
    ['w-10', 'dateFinSouhaite'],
    ['w-5', 'qteDem'],
    ['w-25', 'commentaire'],
  ];

  fields.forEach(function ([classe, fieldName]) {
    if (fieldName !== 'artFams2') {
      createFieldAndAppendTo(classe, prototype, fieldName, row);
    } else {
      createFams2AndAppendTo(classe, prototype, row);
    }
  });

  let div = document.createElement('div');
  div.classList.add('mt-3');

  // Ajouter la row complète dans le container
  prototype.appendChild(row);
  container.appendChild(prototype);
  container.appendChild(div);
}

function replaceNameToNewIndex(element, newIndex) {
  return element.replace('__name__', newIndex);
}

function createFieldAndAppendTo(classe, prototype, fieldName, parentField) {
  let field = document.createElement('div');
  field.classList.add(classe);
  field.appendChild(prototype.querySelector(`[id*="${fieldName}"]`));
  parentField.appendChild(field);
}

function createFams2AndAppendTo(className, prototype, parentField) {
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
