/**
 * Gestionnaire de spinner réutilisable
 * 
 * Ce module fournit des fonctions utilitaires pour gérer l'affichage
 * et le masquage des spinners dans l'application.
 */
class SpinnerManager {
    
    /**
     * Affiche un spinner
     * 
     * @param {string} spinnerId - ID du spinner à afficher
     * @param {Object} options - Options de configuration
     * @param {boolean} options.hideContent - Si le contenu doit être masqué (défaut: false)
     * @param {string} options.contentSelector - Sélecteur du contenu à masquer
     */
    static show(spinnerId, options = {}) {
        const spinner = document.getElementById(spinnerId);
        if (!spinner) {
            console.warn(`Spinner avec l'ID "${spinnerId}" non trouvé`);
            return;
        }

        const { hideContent = false, contentSelector = null } = options;

        // Afficher le spinner
        spinner.style.display = 'inline-block';

        // Masquer le contenu si demandé
        if (hideContent && contentSelector) {
            const content = document.querySelector(contentSelector);
            if (content) {
                content.style.display = 'none';
            }
        }
    }

    /**
     * Masque un spinner
     * 
     * @param {string} spinnerId - ID du spinner à masquer
     * @param {Object} options - Options de configuration
     * @param {boolean} options.showContent - Si le contenu doit être affiché (défaut: false)
     * @param {string} options.contentSelector - Sélecteur du contenu à afficher
     */
    static hide(spinnerId, options = {}) {
        const spinner = document.getElementById(spinnerId);
        if (!spinner) {
            console.warn(`Spinner avec l'ID "${spinnerId}" non trouvé`);
            return;
        }

        const { showContent = false, contentSelector = null } = options;

        // Masquer le spinner
        spinner.style.display = 'none';

        // Afficher le contenu si demandé
        if (showContent && contentSelector) {
            const content = document.querySelector(contentSelector);
            if (content) {
                content.style.display = 'block';
            }
        }
    }

    /**
     * Toggle l'état d'un spinner
     * 
     * @param {string} spinnerId - ID du spinner à basculer
     * @param {Object} options - Options de configuration
     */
    static toggle(spinnerId, options = {}) {
        const spinner = document.getElementById(spinnerId);
        if (!spinner) {
            console.warn(`Spinner avec l'ID "${spinnerId}" non trouvé`);
            return;
        }

        const isVisible = spinner.style.display !== 'none';
        
        if (isVisible) {
            this.hide(spinnerId, options);
        } else {
            this.show(spinnerId, options);
        }
    }

    /**
     * Crée un spinner overlay plein écran
     * 
     * @param {string} spinnerId - ID unique pour le spinner
     * @param {Object} options - Options de configuration
     * @param {string} options.message - Message à afficher avec le spinner
     * @param {string} options.color - Couleur du spinner
     * @param {string} options.size - Taille du spinner
     */
    static createOverlay(spinnerId, options = {}) {
        const { message = 'Chargement...', color = 'primary', size = 'medium' } = options;

        // Vérifier si l'overlay existe déjà
        let overlay = document.getElementById(spinnerId);
        if (overlay) {
            this.show(spinnerId);
            return;
        }

        // Créer l'overlay
        overlay = document.createElement('div');
        overlay.id = spinnerId;
        overlay.className = 'spinner-overlay';
        
        // Créer le contenu de l'overlay
        const content = document.createElement('div');
        content.style.textAlign = 'center';
        content.style.color = 'white';
        
        // Créer le spinner
        const spinner = document.createElement('div');
        spinner.className = `spinner spinner-${size} spinner-${color}`;
        
        // Ajouter les éléments du spinner
        for (let i = 0; i < 12; i++) {
            const div = document.createElement('div');
            spinner.appendChild(div);
        }
        
        // Créer le message
        const messageElement = document.createElement('div');
        messageElement.textContent = message;
        messageElement.style.marginTop = '20px';
        messageElement.style.fontSize = '16px';
        
        // Assembler l'overlay
        content.appendChild(spinner);
        content.appendChild(messageElement);
        overlay.appendChild(content);
        
        // Ajouter au DOM
        document.body.appendChild(overlay);
    }

    /**
     * Supprime un spinner overlay
     * 
     * @param {string} spinnerId - ID du spinner à supprimer
     */
    static removeOverlay(spinnerId) {
        const overlay = document.getElementById(spinnerId);
        if (overlay && overlay.classList.contains('spinner-overlay')) {
            overlay.remove();
        }
    }

    /**
     * Gère automatiquement un spinner pendant une requête AJAX
     * 
     * @param {string} spinnerId - ID du spinner
     * @param {Function} ajaxFunction - Fonction AJAX à exécuter
     * @param {Object} options - Options de configuration
     */
    static async withSpinner(spinnerId, ajaxFunction, options = {}) {
        const { 
            hideContent = false, 
            contentSelector = null,
            showContent = true 
        } = options;

        try {
            // Afficher le spinner
            this.show(spinnerId, { hideContent, contentSelector });
            
            // Exécuter la fonction AJAX
            const result = await ajaxFunction();
            
            return result;
        } catch (error) {
            console.error('Erreur lors de l\'exécution avec spinner:', error);
            throw error;
        } finally {
            // Masquer le spinner
            this.hide(spinnerId, { showContent, contentSelector });
        }
    }
}

/**
 * Fonctions utilitaires globales pour faciliter l'utilisation
 */
window.SpinnerUtils = {
    show: SpinnerManager.show.bind(SpinnerManager),
    hide: SpinnerManager.hide.bind(SpinnerManager),
    toggle: SpinnerManager.toggle.bind(SpinnerManager),
    createOverlay: SpinnerManager.createOverlay.bind(SpinnerManager),
    removeOverlay: SpinnerManager.removeOverlay.bind(SpinnerManager),
    withSpinner: SpinnerManager.withSpinner.bind(SpinnerManager)
};

// Export pour les modules ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SpinnerManager;
}
