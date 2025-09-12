import { FetchManager } from "../api/FetchManager.js";
import { AutoComplete } from "../utils/AutoComplete.js";

/**
 * Gestionnaire d'autocomplétion optimisé pour les fournisseurs
 */
class OptimizedFrsnpManager {
  constructor() {
    this.fetchManager = new FetchManager();
    this.cache = new Map();
    this.debounceTimeout = null;
    this.minSearchLength = 2;
    this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    this.init();
  }

  init() {
    this.setupAutocomplete();
    this.setupCacheCleanup();
  }

  setupAutocomplete() {
    const elements = this.getElements();
    if (!this.validateElements(elements)) {
      console.error('Éléments requis manquants pour l\'autocomplétion');
      return;
    }

    const { frs, suggestionContainer, loader } = elements;

    this.autocomplete = new AutoComplete({
      inputElement: frs,
      suggestionContainer: suggestionContainer,
      loaderElement: loader,
      fetchDataCallback: (query) => this.fetchFrsOptimized(query),
      displayItemCallback: (item) => this.formatDisplayItem(item),
      itemToStringCallback: (item) => this.formatItemString(item),
      onSelectCallback: (item) => this.handleSelection(item, frs),
      minLength: this.minSearchLength,
      debounceDelay: 300
    });
  }

  getElements() {
    return {
      frs: document.getElementById('liste_cde_frn_non_place_search_CodeNomFrs'),
      suggestionContainer: document.getElementById('suggestion-numfrs'),
      loader: document.getElementById('loader-numfrs')
    };
  }

  validateElements(elements) {
    return Object.values(elements).every(element => element !== null);
  }

  async fetchFrsOptimized(query) {
    if (!query || query.length < this.minSearchLength) {
      return [];
    }

    const cacheKey = `frs_${query.toLowerCase()}`;
    
    // Vérifier le cache
    if (this.cache.has(cacheKey)) {
      const cached = this.cache.get(cacheKey);
      if (this.isCacheValid(cached)) {
        return cached.data;
      } else {
        this.cache.delete(cacheKey);
      }
    }

    try {
      const data = await this.fetchManager.get(`frs-non-place-fetch?q=${encodeURIComponent(query)}`);
      
      // Mettre en cache
      this.cache.set(cacheKey, {
        data: data,
        timestamp: Date.now()
      });

      return data;
    } catch (error) {
      console.error('Erreur lors de la récupération des fournisseurs:', error);
      this.showError('Erreur de chargement des fournisseurs');
      return [];
    }
  }

  isCacheValid(cached) {
    return cached && (Date.now() - cached.timestamp) < this.cacheTimeout;
  }

  formatDisplayItem(item) {
    if (!item) return '';
    return `${item.codefrs} - ${item.libfrs}`;
  }

  formatItemString(item) {
    if (!item) return '';
    return `${item.codefrs} - ${item.libfrs}`;
  }

  handleSelection(item, inputElement) {
    if (!item || !inputElement) return;
    
    inputElement.value = item.codefrs;
    
    // Déclencher l'événement change pour les composants qui en dépendent
    inputElement.dispatchEvent(new Event('change', { bubbles: true }));
    
    // Optionnel : déclencher un événement personnalisé
    inputElement.dispatchEvent(new CustomEvent('frsSelected', {
      detail: { item, codefrs: item.codefrs }
    }));
  }

  showError(message) {
    // Logique pour afficher les erreurs (toast, console, etc.)
    console.error(message);
    
    // Optionnel : afficher une notification utilisateur
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: message,
        timer: 3000
      });
    }
  }

  setupCacheCleanup() {
    // Nettoyage périodique du cache
    setInterval(() => {
      this.cleanupCache();
    }, this.cacheTimeout);
  }

  cleanupCache() {
    const now = Date.now();
    for (const [key, value] of this.cache) {
      if (now - value.timestamp > this.cacheTimeout) {
        this.cache.delete(key);
      }
    }
  }

  // Méthodes publiques pour le contrôle externe
  clearCache() {
    this.cache.clear();
  }

  getCacheStats() {
    return {
      size: this.cache.size,
      keys: Array.from(this.cache.keys())
    };
  }

  destroy() {
    if (this.autocomplete) {
      this.autocomplete.destroy();
    }
    this.clearCache();
    if (this.debounceTimeout) {
      clearTimeout(this.debounceTimeout);
    }
  }
}

// Initialisation optimisée
let frsnpManager = null;

function initializeFrsnpManager() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      frsnpManager = new OptimizedFrsnpManager();
    });
  } else {
    frsnpManager = new OptimizedFrsnpManager();
  }
}

// Initialisation
initializeFrsnpManager();

// Export pour utilisation externe
export { OptimizedFrsnpManager, frsnpManager };
