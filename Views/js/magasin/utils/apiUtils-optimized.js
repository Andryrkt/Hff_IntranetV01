import { toggleSpinner } from './spinnerUtils.js';
import { populateServiceOptions, contenuInfoMateriel } from './uiUtils.js';
import { FetchManager } from '../../api/FetchManager.js';

/**
 * Gestionnaire d'API optimisé avec cache et gestion d'erreurs améliorée
 */
class OptimizedApiManager {
  constructor() {
    this.fetchManager = new FetchManager();
    this.cache = new Map();
    this.requestQueue = new Map(); // Pour éviter les requêtes dupliquées
    this.retryAttempts = 3;
    this.retryDelay = 1000;
  }

  /**
   * Récupère les données avec cache et gestion d'erreurs
   */
  async fetchWithCache(url, options = {}) {
    const cacheKey = `${url}_${JSON.stringify(options)}`;
    
    // Vérifier le cache
    if (this.cache.has(cacheKey)) {
      return this.cache.get(cacheKey);
    }

    // Vérifier si une requête est déjà en cours
    if (this.requestQueue.has(cacheKey)) {
      return this.requestQueue.get(cacheKey);
    }

    // Créer la promesse de requête
    const requestPromise = this.executeRequest(url, options);
    this.requestQueue.set(cacheKey, requestPromise);

    try {
      const result = await requestPromise;
      this.cache.set(cacheKey, result);
      return result;
    } catch (error) {
      console.error(`Erreur lors de la requête ${url}:`, error);
      throw error;
    } finally {
      this.requestQueue.delete(cacheKey);
    }
  }

  async executeRequest(url, options = {}) {
    let lastError;
    
    for (let attempt = 1; attempt <= this.retryAttempts; attempt++) {
      try {
        const result = await this.fetchManager.get(url);
        return result;
      } catch (error) {
        lastError = error;
        
        if (attempt < this.retryAttempts) {
          console.warn(`Tentative ${attempt} échouée pour ${url}, nouvelle tentative dans ${this.retryDelay}ms`);
          await this.delay(this.retryDelay * attempt);
        }
      }
    }
    
    throw lastError;
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Récupère les informations matériel avec cache
   */
  async fetchNumMatMarqueCasier(numOr, rectangle) {
    if (!numOr) {
      throw new Error('Numéro OR requis');
    }

    const url = `api/numMat-marq-casier/${numOr}`;
    
    try {
      const data = await this.fetchWithCache(url);
      contenuInfoMateriel(data, rectangle);
      return data;
    } catch (error) {
      console.error('Erreur lors du chargement des informations matériel:', error);
      this.showError(rectangle, 'Erreur de chargement des données');
      throw error;
    }
  }

  /**
   * Récupère les services pour une agence avec cache
   */
  async fetchServicesForAgence(agence, serviceInput, spinnerService, serviceContainer) {
    if (!agence) {
      console.warn('Agence non spécifiée');
      return;
    }

    const url = `service-informix-fetch/${agence}`;
    
    try {
      toggleSpinner(spinnerService, serviceContainer, true);
      const services = await this.fetchWithCache(url);
      populateServiceOptions(services, serviceInput);
      return services;
    } catch (error) {
      console.error('Erreur lors du chargement des services:', error);
      this.showServiceError(serviceInput);
      throw error;
    } finally {
      toggleSpinner(spinnerService, serviceContainer, false);
    }
  }

  showError(element, message) {
    if (element) {
      element.textContent = message;
      element.classList.add('error');
    }
  }

  showServiceError(serviceInput) {
    if (serviceInput) {
      // Vider les options existantes
      serviceInput.innerHTML = '<option value="">Erreur de chargement</option>';
    }
  }

  /**
   * Invalide le cache pour une URL spécifique
   */
  invalidateCache(urlPattern) {
    const regex = new RegExp(urlPattern);
    for (const [key] of this.cache) {
      if (regex.test(key)) {
        this.cache.delete(key);
      }
    }
  }

  /**
   * Vide tout le cache
   */
  clearCache() {
    this.cache.clear();
  }

  /**
   * Obtient les statistiques du cache
   */
  getCacheStats() {
    return {
      size: this.cache.size,
      keys: Array.from(this.cache.keys())
    };
  }
}

// Instance singleton
const apiManager = new OptimizedApiManager();

// Fonctions d'export pour la compatibilité
export function fetchNumMatMarqueCasier(numOr, rectangle) {
  return apiManager.fetchNumMatMarqueCasier(numOr, rectangle);
}

export function fetchServicesForAgence(agence, serviceInput, spinnerService, serviceContainer) {
  return apiManager.fetchServicesForAgence(agence, serviceInput, spinnerService, serviceContainer);
}

// Export de l'instance pour un contrôle avancé
export { apiManager };
