import { initializeAutoCompletionDesi } from "./autocompleteDesignation";
import { initializeAutoCompletionFrn } from "./autocompleteFournisseur";
import {
  createFieldAndAppendTo,
  createFieldAutocompleteAndAppendTo,
  createFileContainerAndAppendTo,
  createFileNamesLabelAndAppendTo,
  createRemoveButtonAndAppendTo,
  formatAllField,
  getTheField,
} from "./field";

let container = document.getElementById("children-container");

export function ajouterUneLigne() {
  let newIndex = parseInt(localStorage.getItem("daAchatLineCounter")) + 1; // Déterminer un index unique pour les nouveaux champs
  localStorage.setItem("daAchatLineCounter", newIndex); // Changer la valeur de newIndex
  let prototype = document
    .getElementById("child-prototype")
    .firstElementChild.cloneNode(true); // Clonage du prototype

  // Mettre à jour dynamiquement les IDs et Names
  prototype.id = replaceNameToNewIndex(prototype.id, newIndex);
  prototype.querySelectorAll("[id], [name]").forEach(function (element) {
    element.id = element.id
      ? replaceNameToNewIndex(element.id, newIndex)
      : element.id;
    element.name = element.name
      ? replaceNameToNewIndex(element.name, newIndex)
      : element.name;
  });

  // Créer la structure Bootstrap "d-flex gap-3"
  let row = document.createElement("div");
  row.classList.add("d-flex", "gap-3");

  let fields = [
    ["w-25", "artDesi"],
    ["w-15", "nomFournisseur"],
    ["w-15", "dateFinSouhaite"],
    ["w-5", "qteDem"],
    ["w-19", "commentaire"],
    ["w-1", "fileNamesLabel"],
    ["w-15", "fileNamesContainer"],
    ["w-2", "estFicheTechnique"],
    ["d-none", "numeroFournisseur"],
    ["d-none", "artConstp"],
    ["d-none", "artRefp"],
    ["d-none", "deleted"],
    ["d-none", "articleStocke"],
    ["d-none", "prixUnitaire"],
    ["d-none", "numeroLigne"],
    ["d-none", "fileNames"],
  ];

  fields.forEach(function ([classe, fieldName]) {
    if (fieldName === "artDesi" || fieldName === "nomFournisseur") {
      createFieldAutocompleteAndAppendTo(classe, prototype, fieldName, row);
    } else if (fieldName === "fileNamesContainer") {
      createFileContainerAndAppendTo(classe, prototype, row);
    } else if (fieldName === "fileNamesLabel") {
      createFileNamesLabelAndAppendTo(classe, prototype, row); // icône trombone + contenant des pièces jointes
    } else {
      createFieldAndAppendTo(classe, prototype, fieldName, row);
    }
  });
  prototype.querySelectorAll(".mb-3").forEach((el) => el.remove()); // supprimer tous les <div class="mb-3"> à l'intérieur de prototype
  createRemoveButtonAndAppendTo(prototype, row);

  let div = document.createElement("div");
  div.classList.add("mt-3", "mb-3");

  // Ajouter la row complète dans le container
  prototype.appendChild(row);
  prototype.appendChild(div);
  container.appendChild(prototype);

  formatAllField(newIndex); // formater les champs à la ligne newIndex
  autocompleteTheFields(newIndex); // autocomplète les champs
}

export function replaceNameToNewIndex(element, newIndex) {
  return element.replace("__name__", newIndex);
}

export function autocompleteTheFields(line) {
  let designation = getTheField(line, "artDesi");
  let nomFournisseur = getTheField(line, "nomFournisseur");

  initializeAutoCompletionDesi(designation);
  initializeAutoCompletionFrn(nomFournisseur);
}
