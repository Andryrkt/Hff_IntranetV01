import {
  hideCells,
  applyRowspanAndClass,
  addSeparatorRow,
} from "./utils/uiUtils.js";
import { fetchNumMatMarqueCasier } from "./utils/apiUtils.js";

/**
 * Gestionnaire de tableaux optimisé avec mise en cache et performance améliorée
 */
export class OptimizedTableHandler {
  constructor() {
    this.cache = new Map();
    this.observer = null;
    this.initIntersectionObserver();
  }

  initIntersectionObserver() {
    // Observer pour le lazy loading des données
    if ('IntersectionObserver' in window) {
      this.observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            this.loadDataForVisibleRow(entry.target);
          }
        });
      }, { rootMargin: '50px' });
    }
  }

  loadDataForVisibleRow(row) {
    // Logique pour charger les données uniquement pour les lignes visibles
    const numOr = this.extractOrNumber(row);
    if (numOr && !this.cache.has(numOr)) {
      this.cache.set(numOr, 'loading');
      // Charger les données de manière asynchrone
    }
  }

  extractOrNumber(row) {
    const cells = Array.from(row.getElementsByTagName("td"));
    return cells[0]?.textContent.trim(); // Ajuster l'index selon la structure
  }

  /**
   * Groupe les lignes avec optimisations de performance
   */
  groupRows(rows, tableBody, cellIndices, addInfo = true) {
    if (!rows || rows.length === 0) {
      console.warn("Aucune ligne à traiter");
      return;
    }

    // Conversion en Array une seule fois pour de meilleures performances
    const rowsArray = Array.from(rows);
    const processedRows = this.processRows(rowsArray, cellIndices, addInfo);
    
    // Application des modifications en batch
    this.applyBatchModifications(processedRows, tableBody);
  }

  processRows(rows, cellIndices, addInfo) {
    let previousValues = this.initializePreviousValues(cellIndices);
    let rowSpanCount = 0;
    let firstRowInGroup = null;
    const modifications = [];

    rows.forEach((currentRow, index) => {
      const cells = Array.from(currentRow.getElementsByTagName("td"));
      const currentValues = this.extractCurrentValues(cells, cellIndices);
      const hasGroupChanged = this.detectGroupChange(currentValues, previousValues);

      if (!previousValues.orNumber) {
        // Première ligne
        firstRowInGroup = currentRow;
        rowSpanCount = 1;
      } else if (hasGroupChanged) {
        // Nouveau groupe détecté
        if (firstRowInGroup) {
          modifications.push({
            type: 'applyRowspan',
            row: firstRowInGroup,
            rowSpanCount,
            cellIndices,
            addInfo
          });
        }
        modifications.push({
          type: 'addSeparator',
          tableBody,
          currentRow
        });
        rowSpanCount = 1;
        firstRowInGroup = currentRow;
      } else {
        // Même groupe
        rowSpanCount++;
        modifications.push({
          type: 'hideCells',
          row: currentRow,
          cellIndices: Object.values(cellIndices)
        });
      }

      previousValues = { ...currentValues };
    });

    // Traitement du dernier groupe
    if (firstRowInGroup) {
      modifications.push({
        type: 'applyRowspan',
        row: firstRowInGroup,
        rowSpanCount,
        cellIndices,
        addInfo
      });
    }

    return modifications;
  }

  initializePreviousValues(cellIndices) {
    return Object.keys(cellIndices).reduce((acc, key) => {
      acc[key] = null;
      return acc;
    }, {});
  }

  extractCurrentValues(cells, cellIndices) {
    return Object.keys(cellIndices).reduce((acc, key) => {
      acc[key] = cells[cellIndices[key]]?.textContent.trim() || "";
      return acc;
    }, {});
  }

  detectGroupChange(currentValues, previousValues) {
    return Object.keys(currentValues).some(
      (key) => previousValues[key] !== currentValues[key]
    );
  }

  applyBatchModifications(modifications, tableBody) {
    // Utilisation de DocumentFragment pour de meilleures performances
    const fragment = document.createDocumentFragment();
    
    modifications.forEach(mod => {
      switch (mod.type) {
        case 'applyRowspan':
          this.applyRowspanOptimized(mod.row, mod.rowSpanCount, mod.cellIndices, mod.addInfo);
          break;
        case 'addSeparator':
          this.addSeparatorOptimized(mod.tableBody, mod.currentRow);
          break;
        case 'hideCells':
          hideCells(mod.row, mod.cellIndices);
          break;
      }
    });
  }

  applyRowspanOptimized(row, rowSpanCount, cellIndices, addInfo) {
    Object.keys(cellIndices).forEach((key) => {
      const cell = row.getElementsByTagName("td")[cellIndices[key]];
      if (cell) {
        cell.rowSpan = rowSpanCount;
        cell.classList.add("rowspan-cell");
        
        // Optimisation : chargement différé des données
        if (key === "ditNumber" && addInfo) {
          this.loadDataWithDebounce(cell, row, cellIndices);
        }
      }
    });
  }

  loadDataWithDebounce(cell, row, cellIndices) {
    // Debounce pour éviter les appels API multiples
    clearTimeout(this.debounceTimeout);
    this.debounceTimeout = setTimeout(() => {
      this.miseEnPlaceRectangleOptimized(cell, row, cellIndices);
    }, 300);
  }

  miseEnPlaceRectangleOptimized(cell, row, cellIndices) {
    const rectangle = document.createElement("div");
    rectangle.textContent = "Chargement...";
    rectangle.classList.add("rectangle", "loading");
    
    cell.insertBefore(rectangle, cell.firstChild);
    
    const numOr = this.extractOrNumber(row);
    if (numOr) {
      // Vérification du cache avant l'appel API
      if (this.cache.has(numOr)) {
        const cachedData = this.cache.get(numOr);
        if (cachedData !== 'loading') {
          this.updateRectangleContent(rectangle, cachedData);
          return;
        }
      }
      
      fetchNumMatMarqueCasier(numOr, rectangle)
        .then(data => {
          this.cache.set(numOr, data);
          this.updateRectangleContent(rectangle, data);
        })
        .catch(error => {
          console.error('Erreur lors du chargement des données:', error);
          rectangle.textContent = 'Erreur de chargement';
          rectangle.classList.remove('loading');
        });
    } else {
      rectangle.textContent = "Numéro OR introuvable";
      rectangle.classList.remove('loading');
    }
  }

  updateRectangleContent(rectangle, data) {
    const content = `
      <div class="materiel-info">
        <div class="info-row">
          <span class="label">ID:</span> ${data.numMat} | 
          <span class="label">Parc:</span> ${data.numParc} | 
          <span class="label">S/N:</span> ${data.numSerie}
        </div>
        <div class="info-row">
          ${data.marque} | ${data.model} | ${data.designation}
        </div>
        <div class="info-row">
          <span class="label">Casier:</span> ${data.casier}
        </div>
      </div>
    `;
    rectangle.innerHTML = content || "N/A";
    rectangle.classList.remove('loading');
  }

  addSeparatorOptimized(tableBody, currentRow) {
    const separatorRow = document.createElement("tr");
    separatorRow.classList.add("separator-row");
    const td = document.createElement("td");
    td.colSpan = currentRow.cells.length;
    td.classList.add("p-0");
    separatorRow.appendChild(td);
    tableBody.insertBefore(separatorRow, currentRow);
  }

  // Méthode de nettoyage
  destroy() {
    if (this.observer) {
      this.observer.disconnect();
    }
    this.cache.clear();
    clearTimeout(this.debounceTimeout);
  }
}

// Fonction de compatibilité pour l'API existante
export function groupRows(rows, tableBody, cellIndices, addInfo = true) {
  const handler = new OptimizedTableHandler();
  handler.groupRows(rows, tableBody, cellIndices, addInfo);
  return handler;
}
