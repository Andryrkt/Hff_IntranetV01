export function hideCells(row, cellIndices) {
  cellIndices.forEach((index) => {
    const cell = row.getElementsByTagName("td")[index];
    if (cell) {
      cell.style.display = "none";
    }
  });
}

export function applyRowspanAndClass(
  row,
  rowSpanCount,
  cellIndices,
  fetchFunction = null,
  addInfo = true
) {
  Object.keys(cellIndices).forEach((key) => {
    const cell = row.getElementsByTagName("td")[cellIndices[key]];
    if (cell) {
      // Appliquer le rowspan et ajouter une classe
      cell.rowSpan = rowSpanCount;
      cell.classList.add("rowspan-cell");
      // console.log("Appliquer rowspan à", row, "avec", rowSpanCount);

      // Si la clé est `ditNumber`, ajouter un rectangle
      if (key === "ditNumber" && fetchFunction && addInfo) {
        miseEnPlaceRectangle(cell, row, cellIndices, fetchFunction);
      }
    }
  });
}

/**
 * affiche le rectangle contenant l'id materiel , marque et casier
 * @param {*} cell
 * @param {*} row
 * @param {*} cellIndices
 * @param {*} fetchFunction
 */
export function miseEnPlaceRectangle(cell, row, cellIndices, fetchFunction) {
  // Créer un élément rectangle
  const rectangle = document.createElement("div");
  rectangle.textContent = "Loading ...";
  rectangle.classList.add("rectangle");

  // Ajouter le rectangle dans la cellule
  cell.insertBefore(rectangle, cell.firstChild);

  // Récupérer la valeur de `orNumber`
  const numOr = row
    .getElementsByTagName("td")
    [cellIndices["orNumber"]]?.textContent.trim();

  if (numOr) {
    // Appeler la fonction fetch avec `numOr` et le rectangle
    fetchFunction(numOr, rectangle);
  } else {
    console.error("La valeur de `orNumber` est introuvable ou vide.");
    rectangle.textContent = "Erreur de chargement";
  }
}

export function addSeparatorRow(tableBody, currentRow) {
  const separatorRow = document.createElement("tr");
  separatorRow.classList.add("separator-row");
  const td = document.createElement("td");
  td.colSpan = currentRow.cells.length;
  td.classList.add("p-0");
  separatorRow.appendChild(td);
  tableBody.insertBefore(separatorRow, currentRow);
}

export function populateServiceOptions(services, serviceInput) {
  // Supprimer toutes les options existantes
  while (serviceInput.options.length > 0) {
    serviceInput.remove(0);
  }

  // Ajouter une option par défaut
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.text = " -- Choisir une service -- ";
  serviceInput.add(defaultOption);

  // Ajouter les options à partir des services récupérés
  services.forEach((service) => {
    const option = document.createElement("option");
    option.value = service.value;
    option.text = service.text;
    serviceInput.add(option);
  });

  // Afficher les nouvelles valeurs et textes des options (pour débogage)
  for (let i = 0; i < serviceInput.options.length; i++) {
    const option = serviceInput.options[i];
    console.log("Value:", option.value, "Text:", option.text);
  }
}

export function contenuInfoMateriel(data, rectangle) {
  // Ajouter le contenu au rectangle
  const contenu = `
   ID: ${data.numMat} | Parc: ${data.numParc} | S/N: ${data.numSerie}<br/>
   ${data.marque} | ${data.model} | ${data.designation}<br/>
   Casier : ${data.casier}<br/>
 `;
  rectangle.innerHTML = contenu || "N/A";
}
