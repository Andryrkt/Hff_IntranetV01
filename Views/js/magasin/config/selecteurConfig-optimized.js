import {
  cellIndicesOrATraiter,
  cellIndicesOrALivrer,
  cellIndicesCisATraiter,
  cellIndicesCisALivrer,
  cellIndicesListePlanning,
  cellIndicesLcfng,
} from "./cellIndicesConfig.js";

/**
 * Configuration optimisée avec validation et cache
 */
class ConfigManager {
  constructor() {
    this.configCache = new Map();
    this.validators = this.createValidators();
  }

  createValidators() {
    return {
      requiredFields: (config) => {
        const required = ['tableBody', 'cellIndices'];
        return required.every(field => config[field] !== undefined);
      },
      selectors: (config) => {
        const selectors = Object.values(config).filter(value => 
          typeof value === 'string' && value.startsWith('#')
        );
        return selectors.every(selector => {
          try {
            return document.querySelector(selector) !== null;
          } catch {
            return false;
          }
        });
      }
    };
  }

  getConfig(pageType) {
    if (this.configCache.has(pageType)) {
      return this.configCache.get(pageType);
    }

    const config = this.buildConfig(pageType);
    if (config) {
      this.configCache.set(pageType, config);
    }
    
    return config;
  }

  buildConfig(pageType) {
    const baseConfig = this.getBaseConfig(pageType);
    if (!baseConfig) return null;

    // Validation de la configuration
    if (!this.validateConfig(baseConfig)) {
      console.error(`Configuration invalide pour ${pageType}`);
      return null;
    }

    return baseConfig;
  }

  getBaseConfig(pageType) {
    const configs = {
      or_a_traiter: {
        tableBody: "#tableBody",
        agenceInput: "#magasin_liste_or_a_traiter_search_agence",
        serviceInput: "#magasin_liste_or_a_traiter_search_service",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        numDitInput: "#magasin_liste_or_a_traiter_search_numDit",
        refPieceInput: "#magasin_liste_or_a_traiter_search_referencePiece",
        numOrInput: "#magasin_liste_or_a_traiter_search_numOr",
        cellIndices: cellIndicesOrATraiter,
        pageType: 'or_a_traiter',
        features: ['tableGrouping', 'serviceLoading', 'inputValidation']
      },
      or_a_livrer: {
        tableBody: "#tableBody",
        agenceInput: "#magasin_liste_or_a_livrer_search_agence",
        serviceInput: "#magasin_liste_or_a_livrer_search_service",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        numDitInput: "#magasin_liste_or_a_livrer_search_numDit",
        refPieceInput: "#magasin_liste_or_a_livrer_search_referencePiece",
        numOrInput: "#magasin_liste_or_a_livrer_search_numOr",
        cellIndices: cellIndicesOrALivrer,
        pageType: 'or_a_livrer',
        features: ['tableGrouping', 'serviceLoading', 'inputValidation']
      },
      cis_a_traiter: {
        tableBody: "#tableBody",
        agenceInput: "#a_traiter_search_agence",
        serviceInput: "#a_traiter_search_service",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        numDitInput: "#a_traiter_search_numDit",
        refPieceInput: "#a_traiter_search_referencePiece",
        numOrInput: "#a_traiter_search_numOr",
        cellIndices: cellIndicesCisATraiter,
        pageType: 'cis_a_traiter',
        features: ['tableGrouping', 'serviceLoading', 'inputValidation']
      },
      cis_a_livrer: {
        tableBody: "#tableBody",
        agenceInput: "#a_livrer_search_agence",
        serviceInput: "#a_livrer_search_service",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        numDitInput: "#a_livrer_search_numDit",
        refPieceInput: "#a_livrer_search_referencePiece",
        numOrInput: "#a_livrer_search_numOr",
        cellIndices: cellIndicesCisALivrer,
        pageType: 'cis_a_livrer',
        features: ['tableGrouping', 'serviceLoading', 'inputValidation']
      },
      liste_planning: {
        tableBody: "#tableBody",
        cellIndices: cellIndicesListePlanning,
        pageType: 'liste_planning',
        features: ['tableGrouping']
      },
      liste_cde_fnr_non_genere: {
        tableBody: "#tableBody",
        agenceEmetteurInput: "#liste_cde_frn_non_generer_search_agenceEmetteur",
        serviceEmetteurInput: "#liste_cde_frn_non_generer_search_serviceEmetteur",
        agenceInput: "#liste_cde_frn_non_generer_search_agence",
        serviceInput: "#liste_cde_frn_non_generer_search_service",
        spinnerServiceEmetteur: "#spinner-service-emetteur",
        serviceContainerEmetteur: "#service-container-emetteur",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        numDitInput: "#liste_cde_frn_non_generer_search_numDit",
        refPieceInput: "#liste_cde_frn_non_generer_search_referencePiece",
        numDocInput: "#liste_cde_frn_non_generer_search_numDoc",
        cellIndices: cellIndicesLcfng,
        pageType: 'liste_cde_fnr_non_genere',
        features: ['serviceLoading', 'inputValidation', 'dualAgence']
      },
      liste_cde_fnr_non_place: {
        tableBody: "#tableBody",
        agenceInput: "#liste_cde_frn_non_place_search_agence",
        serviceInput: "#liste_cde_frn_non_place_search_service",
        spinnerService: "#spinner-service",
        serviceContainer: "#service-container",
        cellIndices: cellIndicesLcfng,
        pageType: 'liste_cde_fnr_non_place',
        features: ['serviceLoading']
      }
    };

    return configs[pageType] || null;
  }

  validateConfig(config) {
    return this.validators.requiredFields(config);
  }

  // Méthodes utilitaires
  hasFeature(pageType, feature) {
    const config = this.getConfig(pageType);
    return config && config.features && config.features.includes(feature);
  }

  getElement(pageType, elementKey) {
    const config = this.getConfig(pageType);
    return config ? config[elementKey] : null;
  }

  clearCache() {
    this.configCache.clear();
  }

  getCacheStats() {
    return {
      size: this.configCache.size,
      keys: Array.from(this.configCache.keys())
    };
  }
}

// Instance singleton
const configManager = new ConfigManager();

// Configuration de compatibilité pour l'API existante
export const config = new Proxy({}, {
  get(target, pageType) {
    return configManager.getConfig(pageType);
  }
});

// Export de l'instance pour un contrôle avancé
export { configManager };
