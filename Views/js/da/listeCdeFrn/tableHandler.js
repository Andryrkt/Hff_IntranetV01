/**
 * Fusion verticale + séparateurs, sans casser le rowSpan
 * – Insert <tr> vide pour le **niveau racine** seulement
 * – Pour les niveaux suivants => simple bordure CSS
 */
export function mergeCellsRecursiveTable(groups, tbodyId = "tableBody") {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;

  // vraies lignes (sans les éventuels séparateurs déjà présents)
  const dataRows = Array.from(tbody.querySelectorAll("tr:not(.separator-row)"));

  /* ---------- helpers DOM (déplacés en haut pour éviter l’erreur) ---------- */
  const cell = (row, i) => row.querySelectorAll("td")[i];
  const text = (row, i) => cell(row, i)?.textContent.trim() ?? "";

  /* ------------------------------------------------------------------ */
  /* 1.  Traitement récursif                                            */
  /* ------------------------------------------------------------------ */
  recurse(dataRows, 0, /*isRoot=*/ true);

  /* 2.  Séparateur final (bande pleine)                                */
  if (dataRows.length) insertFullSeparatorAfter(dataRows[dataRows.length - 1]);

  /* ================================================================== */
  /* -----------------  Fonctions internes  --------------------------- */
  /* ================================================================== */

  /** Traite un niveau hiérarchique. */
  function recurse(rows, level, isRoot) {
    if (level >= groups.length) return;

    const { pivotIndex, columns, insertSeparator = false } = groups[level];

    let start = 0;
    while (start < rows.length) {
      const refVal = text(rows[start], pivotIndex);
      let end = start + 1;

      // Repérer le bloc ayant la même valeur pivot
      while (end < rows.length && text(rows[end], pivotIndex) === refVal) end++;

      const block = rows.slice(start, end); // lignes du groupe
      mergeBlock(block, columns); // fusion du niveau
      recurse(block, level + 1, /*isRoot=*/ false); // traiter le sous-niveau

      // Séparateur entre groupes
      if (insertSeparator && end < rows.length) {
        if (isRoot) {
          insertFullSeparatorBefore(rows[end]); // <tr> vide (bande)
        } else {
          rows[end].classList.add("sub-separator"); // simple trait CSS
        }
      }

      start = end;
    }
  }

  /** Applique le rowspan dans un bloc et masque lignes 2…n. */
  function mergeBlock(blockRows, cols) {
    const span = blockRows.length;
    if (span <= 1) return;

    // masque cellules redondantes
    blockRows
      .slice(1)
      .forEach((row) =>
        cols.forEach((i) => (cell(row, i).style.display = "none"))
      );

    // rowspan sur la 1ʳᵉ ligne
    cols.forEach((i) => {
      const c = cell(blockRows[0], i);
      c.rowSpan = span;
      c.classList.add("rowspan-cell");
    });
  }

  /* ---------- helpers DOM ---------- */

  function insertFullSeparatorBefore(refRow) {
    tbody.insertBefore(makeSeparator(refRow.cells.length), refRow);
  }
  function insertFullSeparatorAfter(refRow) {
    tbody.insertBefore(makeSeparator(refRow.cells.length), refRow.nextSibling);
  }
  function makeSeparator(colspan) {
    const tr = document.createElement("tr");
    tr.className = "separator-row";
    const td = document.createElement("td");
    td.colSpan = colspan;
    td.className = "p-0";
    tr.appendChild(td);
    return tr;
  }
}
