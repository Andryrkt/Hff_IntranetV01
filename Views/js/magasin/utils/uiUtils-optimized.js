/**
 * Utilitaires UI optimisés avec performance et accessibilité améliorées
 */

/**
 * Cache pour les éléments DOM fréquemment utilisés
 */
const domCache = new Map();

/**
 * Récupère un élément DOM avec cache
 */
function getCachedElement(selector) {
  if (!domCache.has(selector)) {
    const element = document.querySelector(selector);
    if (element) {
      domCache.set(selector, element);
    }
  }
  return domCache.get(selector);
}

/**
 * Masque les cellules avec optimisations de performance
 */
export function hideCells(row, cellIndices) {
  if (!row || !cellIndices || cellIndices.length === 0) {
    console.warn('Paramètres invalides pour hideCells');
    return;
  }

  // Utilisation de requestAnimationFrame pour de meilleures performances
  requestAnimationFrame(() => {
    const cells = row.getElementsByTagName("td");
    cellIndices.forEach((index) => {
      if (cells[index]) {
        cells[index].style.display = "none";
        cells[index].setAttribute('aria-hidden', 'true');
      }
    });
  });
}

/**
 * Applique rowspan et classes avec optimisations
 */
export function applyRowspanAndClass(row, rowSpanCount, cellIndices, fetchFunction = null, addInfo = true) {
  if (!row || !cellIndices || rowSpanCount <= 0) {
    console.warn('Paramètres invalides pour applyRowspanAndClass');
    return;
  }

  const cells = row.getElementsByTagName("td");
  
  Object.keys(cellIndices).forEach((key) => {
    const cellIndex = cellIndices[key];
    const cell = cells[cellIndex];
    
    if (cell) {
      // Appliquer rowspan et classe
      cell.rowSpan = rowSpanCount;
      cell.classList.add("rowspan-cell");
      
      // Ajouter des attributs d'accessibilité
      cell.setAttribute('aria-rowspan', rowSpanCount);
      cell.setAttribute('role', 'cell');
      
      // Gestion spéciale pour ditNumber
      if (key === "ditNumber" && fetchFunction && addInfo) {
        miseEnPlaceRectangleOptimized(cell, row, cellIndices, fetchFunction);
      }
    }
  });
}

/**
 * Place un rectangle d'information avec optimisations
 */
export function miseEnPlaceRectangle(cell, row, cellIndices, fetchFunction) {
  miseEnPlaceRectangleOptimized(cell, row, cellIndices, fetchFunction);
}

function miseEnPlaceRectangleOptimized(cell, row, cellIndices, fetchFunction) {
  // Vérifier si un rectangle existe déjà
  const existingRectangle = cell.querySelector('.rectangle');
  if (existingRectangle) {
    return; // Éviter les doublons
  }

  const rectangle = createRectangleElement();
  cell.insertBefore(rectangle, cell.firstChild);

  const numOr = extractOrNumber(row, cellIndices);
  if (numOr) {
    loadRectangleData(numOr, rectangle, fetchFunction);
  } else {
    showRectangleError(rectangle, "Numéro OR introuvable");
  }
}

function createRectangleElement() {
  const rectangle = document.createElement("div");
  rectangle.className = "rectangle loading";
  rectangle.setAttribute('role', 'status');
  rectangle.setAttribute('aria-live', 'polite');
  rectangle.textContent = "Chargement...";
  return rectangle;
}

function extractOrNumber(row, cellIndices) {
  const cells = Array.from(row.getElementsByTagName("td"));
  const orNumberIndex = cellIndices["orNumber"];
  return cells[orNumberIndex]?.textContent.trim();
}

async function loadRectangleData(numOr, rectangle, fetchFunction) {
  try {
    await fetchFunction(numOr, rectangle);
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error);
    showRectangleError(rectangle, 'Erreur de chargement');
  }
}

function showRectangleError(rectangle, message) {
  rectangle.textContent = message;
  rectangle.className = "rectangle error";
}

/**
 * Ajoute une ligne séparatrice avec optimisations
 */
export function addSeparatorRow(tableBody, currentRow) {
  if (!tableBody || !currentRow) {
    console.warn('Paramètres invalides pour addSeparatorRow');
    return;
  }

  const separatorRow = createSeparatorRow(currentRow.cells.length);
  tableBody.insertBefore(separatorRow, currentRow);
}

function createSeparatorRow(colspan) {
  const separatorRow = document.createElement("tr");
  separatorRow.className = "separator-row";
  separatorRow.setAttribute('role', 'separator');
  
  const td = document.createElement("td");
  td.colSpan = colspan;
  td.className = "p-0";
  td.setAttribute('aria-hidden', 'true');
  
  separatorRow.appendChild(td);
  return separatorRow;
}

/**
 * Peuple les options de service avec optimisations
 */
export function populateServiceOptions(services, serviceInput) {
  if (!serviceInput || !Array.isArray(services)) {
    console.warn('Paramètres invalides pour populateServiceOptions');
    return;
  }

  // Utilisation de DocumentFragment pour de meilleures performances
  const fragment = document.createDocumentFragment();
  
  // Option par défaut
  const defaultOption = createOption("", " -- Choisir un service -- ");
  defaultOption.setAttribute('disabled', '');
  fragment.appendChild(defaultOption);

  // Options des services
  services.forEach((service) => {
    const option = createOption(service.value, service.text);
    fragment.appendChild(option);
  });

  // Remplacement en une seule opération
  serviceInput.innerHTML = '';
  serviceInput.appendChild(fragment);
  
  // Déclencher l'événement change pour les composants qui en dépendent
  serviceInput.dispatchEvent(new Event('change', { bubbles: true }));
}

function createOption(value, text) {
  const option = document.createElement("option");
  option.value = value;
  option.textContent = text;
  return option;
}

/**
 * Affiche le contenu des informations matériel avec optimisations
 */
export function contenuInfoMateriel(data, rectangle) {
  if (!rectangle) {
    console.warn('Rectangle non fourni pour contenuInfoMateriel');
    return;
  }

  if (!data) {
    showRectangleError(rectangle, "Aucune donnée disponible");
    return;
  }

  const content = createMaterielContent(data);
  rectangle.innerHTML = content;
  rectangle.className = "rectangle loaded";
}

function createMaterielContent(data) {
  return `
    <div class="materiel-info">
      <div class="info-row">
        <span class="label">ID:</span> 
        <span class="value">${escapeHtml(data.numMat || 'N/A')}</span> | 
        <span class="label">Parc:</span> 
        <span class="value">${escapeHtml(data.numParc || 'N/A')}</span> | 
        <span class="label">S/N:</span> 
        <span class="value">${escapeHtml(data.numSerie || 'N/A')}</span>
      </div>
      <div class="info-row">
        <span class="value">${escapeHtml(data.marque || '')} | ${escapeHtml(data.model || '')} | ${escapeHtml(data.designation || '')}</span>
      </div>
      <div class="info-row">
        <span class="label">Casier:</span> 
        <span class="value">${escapeHtml(data.casier || 'N/A')}</span>
      </div>
    </div>
  `;
}

/**
 * Échappe les caractères HTML pour la sécurité
 */
function escapeHtml(text) {
  if (typeof text !== 'string') return text;
  
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Utilitaires de validation
 */
export const validators = {
  isElement: (element) => element instanceof HTMLElement,
  isArray: (arr) => Array.isArray(arr),
  isString: (str) => typeof str === 'string',
  isNumber: (num) => typeof num === 'number' && !isNaN(num)
};

/**
 * Nettoyage du cache DOM
 */
export function clearDomCache() {
  domCache.clear();
}

/**
 * Obtient les statistiques du cache DOM
 */
export function getDomCacheStats() {
  return {
    size: domCache.size,
    keys: Array.from(domCache.keys())
  };
}
