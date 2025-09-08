import { groupRows } from "./tableHandler.js";
import { fetchServicesForAgence } from "./utils/serviceApiUtils.js";
import { toUppercase, allowOnlyNumbers } from "./utils/inputUtils.js";
import { config } from "./config/selecteurConfig.js";

/** ================================================
 * Configuration dynamique en fonction de la page
 ====================================================*/

class MagasinPageManager {
  constructor() {
    this.pageType = this.detectPageType();
    this.currentConfig = this.loadConfiguration();
    this.cache = new Map(); // Cache pour éviter les requêtes répétées
    this.init();
  }

  detectPageType() {
    const pageType = document.querySelector("#conteneur")?.dataset.pageType;
    if (!pageType) {
      console.error("Type de page non détecté");
      return null;
    }
    return pageType;
  }

  loadConfiguration() {
    if (!this.pageType || !config[this.pageType]) {
      console.error("Configuration introuvable pour cette page.");
      return null;
    }
    return config[this.pageType];
  }

  init() {
    if (!this.currentConfig) return;

    this.initializeTableHandling();
    this.initializeServiceHandling();
    this.initializeInputHandling();
  }

  initializeTableHandling() {
    const tableBody = document.querySelector(this.currentConfig.tableBody);
    const rows = document.querySelectorAll(`${this.currentConfig.tableBody} tr`);
    
    if (!tableBody || rows.length === 0) {
      console.warn("Tableau non trouvé ou vide");
      return;
    }

    // Optimisation : ne pas grouper pour certaines pages
    const skipGrouping = ["liste_cde_fnr_non_genere", "liste_cde_fnr_non_place"];
    if (!skipGrouping.includes(this.pageType)) {
      groupRows(rows, tableBody, this.currentConfig.cellIndices);
    }
  }

  initializeServiceHandling() {
    this.setupServiceHandlers();
    
    // Gestion spéciale pour les pages avec agence émettrice
    if (this.pageType === "liste_cde_fnr_non_genere") {
      this.setupEmetteurServiceHandlers();
    }
  }

  setupServiceHandlers() {
    const agenceInput = document.querySelector(this.currentConfig.agenceInput);
    const serviceInput = document.querySelector(this.currentConfig.serviceInput);
    const spinnerService = document.querySelector(this.currentConfig.spinnerService);
    const serviceContainer = document.querySelector(this.currentConfig.serviceContainer);

    if (!this.validateElements([agenceInput, serviceInput, spinnerService, serviceContainer])) {
      return;
    }

    // Utilisation de la délégation d'événements pour de meilleures performances
    agenceInput.addEventListener("change", (e) => {
      this.handleAgenceChange(e.target, serviceInput, spinnerService, serviceContainer);
    });
  }

  setupEmetteurServiceHandlers() {
    const elements = {
      agence: document.querySelector(this.currentConfig.agenceEmetteurInput),
      service: document.querySelector(this.currentConfig.serviceEmetteurInput),
      spinner: document.querySelector(this.currentConfig.spinnerServiceEmetteur),
      container: document.querySelector(this.currentConfig.serviceContainerEmetteur)
    };

    if (!this.validateElements(Object.values(elements))) {
      return;
    }

    elements.agence.addEventListener("change", (e) => {
      this.handleAgenceChange(e.target, elements.service, elements.spinner, elements.container);
    });
  }

  async handleAgenceChange(agenceInput, serviceInput, spinnerService, serviceContainer) {
    const agence = agenceInput.value.split("-")[0];
    
    // Vérification du cache
    const cacheKey = `services_${agence}`;
    if (this.cache.has(cacheKey)) {
      this.populateServicesFromCache(serviceInput, this.cache.get(cacheKey));
      return;
    }

    try {
      await fetchServicesForAgence(agence, serviceInput, spinnerService, serviceContainer);
      // Mise en cache des services (optionnel)
      // this.cache.set(cacheKey, services);
    } catch (error) {
      console.error("Erreur lors du chargement des services:", error);
    }
  }

  populateServicesFromCache(serviceInput, services) {
    // Logique pour peupler les services depuis le cache
    // Implementation selon les besoins
  }

  initializeInputHandling() {
    this.setupUppercaseHandlers();
    this.setupNumberValidation();
  }

  setupUppercaseHandlers() {
    const inputs = [
      { selector: this.currentConfig.numDitInput, handler: toUppercase },
      { selector: this.currentConfig.refPieceInput, handler: toUppercase }
    ];

    inputs.forEach(({ selector, handler }) => {
      const input = document.querySelector(selector);
      if (input) {
        // Utilisation de l'événement 'input' avec debounce pour de meilleures performances
        let timeout;
        input.addEventListener("input", (e) => {
          clearTimeout(timeout);
          timeout = setTimeout(() => handler(e.target), 100);
        });
      }
    });
  }

  setupNumberValidation() {
    const inputSelector = this.pageType === "liste_cde_fnr_non_genere" 
      ? this.currentConfig.numDocInput 
      : this.currentConfig.numOrInput;

    const input = document.querySelector(inputSelector);
    if (input) {
      let timeout;
      input.addEventListener("input", (e) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => allowOnlyNumbers(e.target), 50);
      });
    }
  }

  validateElements(elements) {
    const invalidElements = elements.filter(el => !el);
    if (invalidElements.length > 0) {
      console.warn(`${invalidElements.length} éléments manquants pour la configuration ${this.pageType}`);
      return false;
    }
    return true;
  }
}

// Initialisation optimisée avec vérification de la disponibilité du DOM
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => new MagasinPageManager());
} else {
  new MagasinPageManager();
}
