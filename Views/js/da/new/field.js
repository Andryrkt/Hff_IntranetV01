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
  // console.log(fieldName, field);

  let dateFinSouhaitee = document.getElementById(
    'demande_appro_form_dateFinSouhaite'
  ).value;
  field.required = ![
    'commentaire',
    'catalogue',
    'numeroLigne',
    'fileNames',
    'artRefp',
    'numeroFournisseur',
    'estFicheTechnique',
    'deleted',
  ].includes(fieldName);

  if (fieldName === 'dateFinSouhaite') {
    field.value = dateFinSouhaitee;
  } else if (fieldName === 'artConstp') {
    field.value = 'ZST';
  } else if (fieldName === 'numeroLigne') {
    field.value = localStorage.getItem('index');
  } else if (fieldName === 'fileNames') {
    field.accept = '.pdf, image/*'; // Accepter les fichiers PDF et images
    field.addEventListener('change', (event) => onFileNamesInputChange(event));
  }

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
  fams2Field.required = true; // champ requis

  // Effacer tous les options
  resetDropdown(fams2Field, '-- Choisir une sous-famille --');

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = fams2Field.id.replace('demande_appro_form_DAL', '');

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement('div');
  spinnerContainer.classList.add('spinner-container');
  spinnerContainer.innerHTML = `
    <div class="spinner-load m-auto" id="spinner${baseId}" style="display: none;margin-bottom: 0px !important;transform: translateY(-2px);">
      ${'<div></div>'.repeat(12)} 
    </div>
  `;

  // Création du conteneur de l'élément cible
  let containerDiv = document.createElement('div');
  containerDiv.id = `container${baseId}`;
  containerDiv.appendChild(fams2Field);

  // Ajout des éléments au conteneur principal
  field.append(spinnerContainer, containerDiv);

  // Ajout du conteneur principal au parent
  parentField.appendChild(field);
}

export function createFileContainerAndAppendTo(
  className,
  prototype,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement('div');
  fieldContainer.classList.add(className);

  fieldContainer.id = prototype
    .querySelector(`[id*="fileNames"]`)
    .id.replace('fileNames', 'fileNamesContainer'); // Génération de l'ID pour le conteneur

  parentField.appendChild(fieldContainer);
}

export function createFileNamesLabelAndAppendTo(
  className,
  prototype,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement('div');
  fieldContainer.classList.add(className);

  // Sélection de l'élément cible
  let fieldFileNames = prototype.querySelector(`[id*="fileNames"]`);

  let icon = document.createElement('i');
  icon.classList.add('fas', 'fa-paperclip', 'text-primary');
  icon.title = 'Mettre en pièces jointes un ou plusieur(s) fichier(s)';
  icon.style.cursor = 'pointer';

  icon.addEventListener('click', function () {
    // Ouvrir le sélecteur de fichiers
    fieldFileNames.click();
  });

  // Append the label and field to the container
  fieldContainer.append(icon);
  parentField.appendChild(fieldContainer);
}

export function createFieldAutocompleteAndAppendTo(
  className,
  prototype,
  fieldName,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement('div');
  fieldContainer.classList.add(className);

  // Sélection de l'élément cible
  let field = prototype.querySelector(`[id*="${fieldName}"]`);
  field.required = true; // champ requis

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = field.id.replace('demande_appro_form_DAL', '');

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement('div');
  spinnerContainer.id = `spinner_container${baseId}`;
  spinnerContainer.style.display = 'none';
  spinnerContainer.classList.add('text-center');
  if (fieldName === 'artDesi') {
    spinnerContainer.innerHTML = `<div class="text-overlay">Veuillez patienter s'il vous plaît! Chargement des données </div><div class="loader-points"></div>`;
  }

  // Création du conteneur de l'élément cible
  let containerDiv = document.createElement('div');
  containerDiv.id = `suggestion${baseId}`;
  containerDiv.classList.add('suggestions-container');

  // Ajout des éléments au conteneur principal
  fieldContainer.append(field, containerDiv, spinnerContainer);

  // Ajout du conteneur principal au parent
  parentField.appendChild(fieldContainer);
}

export function formatAllField(line) {
  let designation = getTheField(line, 'artDesi');
  let fournisseur = getTheField(line, 'nomFournisseur');
  let quantite = getTheField(line, 'qteDem');
  designation.addEventListener('input', function () {
    designation.value = designation.value.toUpperCase();
  });
  fournisseur.addEventListener('input', function () {
    fournisseur.value = fournisseur.value.toUpperCase();
  });
  quantite.addEventListener('input', function () {
    quantite.value = quantite.value.replace(/[^\d]/g, '');
  });
}

export function getTheField(
  line,
  fieldName,
  prefixId = 'demande_appro_form_DAL'
) {
  return document.getElementById(`${prefixId}_${line}_${fieldName}`);
}

export function onFileNamesInputChange(event) {
  let inputFile = event.target; // input file field
  let fieldContainer = document.getElementById(
    inputFile.id.replace('fileNames', 'fileNamesContainer')
  ); // Conteneur du champ de fichier correspondant

  // Vérifier si un fichier a été sélectionné
  if (inputFile.files.length > 0) {
    // Vider le conteneur avant d'ajouter les nouveaux liens
    fieldContainer.innerHTML = ''; // Vider le conteneur

    let ul = document.createElement('ul');
    ul.classList.add('ps-3', 'mb-0'); // Ajouter des classes pour le style
    for (let index = 0; index < inputFile.files.length; index++) {
      const file = inputFile.files[index];
      let li = document.createElement('li');
      let a = document.createElement('a');
      a.href = URL.createObjectURL(file);
      a.textContent = file.name; // Afficher le nom du fichier
      a.target = '_blank'; // Ouvrir le fichier dans un nouvel onglet
      li.appendChild(a); // Ajouter le lien à l'élément de liste
      ul.appendChild(li); // Ajouter l'élément de liste
    }
    fieldContainer.appendChild(ul); // Ajouter le lien au conteneur
  }
}
