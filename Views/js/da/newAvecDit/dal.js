import { initializeAutoCompletionDesi } from "./autocompleteDesignation";
import { initializeAutoCompletionFrn } from "./autocompleteFournisseur";
import { eventOnFamille } from "./event";
import {
  createFams2AndAppendTo,
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
  let newIndex = parseInt(localStorage.getItem("daWithDitLineCounter")) + 1; // Déterminer un index unique pour les nouveaux champs à partir du compteur enregistré
  localStorage.setItem("daWithDitLineCounter", newIndex); // Changer la valeur de newIndex
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
    ["w-10", "codeFams1"],
    ["w-10", "codeFams2"],
    ["w-7", "artRefp"],
    ["w-20", "artDesi"],
    ["w-10", "nomFournisseur"],
    ["w-10", "dateFinSouhaite"],
    ["w-5", "qteDem"],
    ["w-13", "commentaire"],
    ["w-1", "fileNamesLabel"],
    ["w-9", "fileNamesContainer"],
    ["w-2", "estFicheTechnique"],
    ["d-none", "artConstp"],
    ["d-none", "artFams1"],
    ["d-none", "artFams2"],
    ["d-none", "numeroFournisseur"],
    ["d-none", "catalogue"],
    ["d-none", "deleted"],
    ["d-none", "numeroLigne"],
    ["d-none", "fileNames"],
  ];

  fields.forEach(function ([classe, fieldName]) {
    if (fieldName === "codeFams2") {
      createFams2AndAppendTo(classe, prototype, row);
    } else if (fieldName === "artDesi" || fieldName === "nomFournisseur") {
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

  if (!devisDitAjoutes) {
    devisDitAjoutes = true;
    ajouterDevisDit(newIndex);
  }

  eventOnFamille(newIndex); // gestion d'évènement sur la famille et sous-famille à la ligne newIndex
  formatAllField(newIndex); // formater les champs à la ligne newIndex
  autocompleteTheFields(newIndex); // autocomplète les champs
}

let devisDitAjoutes = false;

/** Affiche sur la ligne les devis de la DIT (Préparation vente / 90 / COM) et les déclare dans le champ caché devisDit */
function ajouterDevisDit(line) {
  let devis = JSON.parse(container.dataset.devisDit || "[]");
  if (devis.length === 0) return;

  const hidden = getTheField(line, "devisDit");
  const fileContainer = getTheField(line, "fileNamesContainer");
  const liste = document.createElement("ul");
  liste.classList.add("ps-0", "mb-0", "devis-dit-list");

  devis.forEach((nom) => {
    const item = document.createElement("li");
    item.classList.add("file-item");
    item.innerHTML = `<span class="file-name"><a target="_blank"></a></span><span class="remove-file">x</span>`;
    const lien = item.querySelector("a");
    lien.href = `${container.dataset.devisDitUrl}/${encodeURIComponent(nom)}`;
    lien.textContent = nom;
    item.querySelector(".remove-file").addEventListener("click", () => {
      devis = devis.filter((d) => d !== nom);
      hidden.value = devis.join(",");
      item.remove();
    });
    liste.appendChild(item);
  });

  hidden.value = devis.join(",");
  fileContainer.prepend(liste);
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
