/**
 * Gestionnaire principal pour les DOM
 */
class DomManager {
    constructor() {
        this.apiBaseUrl = '/api/dom';
        this.currentStep = 1;
        this.formData = {};
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeForm();
    }

    bindEvents() {
        // Validation en temps réel du matricule
        const matriculeInput = document.querySelector('#dom_form2_matricule');
        if (matriculeInput) {
            matriculeInput.addEventListener('blur', (e) => this.validateMatricule(e.target.value));
        }

        // Vérification des chevauchements de dates
        const dateInputs = document.querySelectorAll('#dom_form2_dateDebut, #dom_form2_dateFin');
        dateInputs.forEach(input => {
            input.addEventListener('change', () => this.checkDateOverlap());
        });

        // Chargement dynamique des catégories selon le type de mission
        const typeMissionSelect = document.querySelector('#dom_form2_sousTypeDocument');
        if (typeMissionSelect) {
            typeMissionSelect.addEventListener('change', () => this.loadCategories());
        }

        // Chargement dynamique des sites selon la catégorie
        const categorieSelect = document.querySelector('#dom_form2_categorie');
        if (categorieSelect) {
            categorieSelect.addEventListener('change', () => this.loadSites());
        }

        // Calcul automatique des indemnités
        const indemnityInputs = document.querySelectorAll('#dom_form2_categorie, #dom_form2_site, #dom_form2_nombreJour');
        indemnityInputs.forEach(input => {
            input.addEventListener('change', () => this.calculateIndemnities());
        });

        // Validation du formulaire avant soumission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => this.validateForm(e));
        }
    }

    initializeForm() {
        // Initialiser les champs selon le type de salarié
        this.toggleEmployeeFields();
        
        // Charger les données de l'étape 1 si disponibles
        this.loadStep1Data();
    }

    toggleEmployeeFields() {
        const salarieSelect = document.querySelector('#dom_form1_salarie');
        if (!salarieSelect) return;

        const permanentFields = document.querySelectorAll('#Interne .form-group');
        const temporaireFields = document.querySelectorAll('#externe .form-group');

        salarieSelect.addEventListener('change', (e) => {
            const isPermanent = e.target.value === 'PERMANENT';
            
            permanentFields.forEach(field => {
                field.style.display = isPermanent ? 'block' : 'none';
            });
            
            temporaireFields.forEach(field => {
                field.style.display = isPermanent ? 'none' : 'block';
            });
        });
    }

    async validateMatricule(matricule) {
        if (!matricule) return;

        try {
            const response = await fetch(`${this.apiBaseUrl}/validate-matricule`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ matricule })
            });

            const result = await response.json();
            this.displayValidationResult('matricule', result);
            
            if (result.valid) {
                this.loadEmployeeInfo(matricule);
            }
        } catch (error) {
            console.error('Erreur lors de la validation du matricule:', error);
            this.showError('Erreur lors de la validation du matricule');
        }
    }

    async checkDateOverlap() {
        const matricule = document.querySelector('#dom_form2_matricule')?.value;
        const dateDebut = document.querySelector('#dom_form2_dateDebut')?.value;
        const dateFin = document.querySelector('#dom_form2_dateFin')?.value;

        if (!matricule || !dateDebut || !dateFin) return;

        try {
            const response = await fetch(`${this.apiBaseUrl}/check-date-overlap`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    matricule,
                    dateDebut,
                    dateFin
                })
            });

            const result = await response.json();
            
            if (result.success && result.hasOverlap) {
                this.showWarning(result.data.message);
            } else {
                this.hideWarning();
            }
        } catch (error) {
            console.error('Erreur lors de la vérification des dates:', error);
        }
    }

    async loadCategories() {
        const typeMissionId = document.querySelector('#dom_form2_sousTypeDocument')?.value;
        const categorieSelect = document.querySelector('#dom_form2_categorie');
        
        if (!typeMissionId || !categorieSelect) return;

        try {
            // Afficher un indicateur de chargement
            categorieSelect.disabled = true;
            categorieSelect.innerHTML = '<option value="">Chargement...</option>';

            const response = await fetch(`${this.apiBaseUrl}/categories/${typeMissionId}`);
            const result = await response.json();
            
            if (result.success) {
                // Vider le select
                categorieSelect.innerHTML = '<option value="">-- Choisir une catégorie --</option>';
                
                // Ajouter les options
                result.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = `${category.code} - ${category.libelle}`;
                    categorieSelect.appendChild(option);
                });
                
                // Réactiver le select
                categorieSelect.disabled = false;
                
                // Vider le champ site car il dépend de la catégorie
                const siteSelect = document.querySelector('#dom_form2_site');
                if (siteSelect) {
                    siteSelect.innerHTML = '<option value="">-- Choisir d\'abord une catégorie --</option>';
                    siteSelect.disabled = true;
                }
            } else {
                categorieSelect.innerHTML = '<option value="">Aucune catégorie disponible</option>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des catégories:', error);
            categorieSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        } finally {
            categorieSelect.disabled = false;
        }
    }

    async loadSites() {
        const categorieId = document.querySelector('#dom_form2_categorie')?.value;
        const siteSelect = document.querySelector('#dom_form2_site');
        
        if (!categorieId || !siteSelect) return;

        try {
            // Afficher un indicateur de chargement
            siteSelect.disabled = true;
            siteSelect.innerHTML = '<option value="">Chargement...</option>';

            const response = await fetch(`${this.apiBaseUrl}/sites/${categorieId}`);
            const result = await response.json();
            
            if (result.success) {
                // Vider le select
                siteSelect.innerHTML = '<option value="">-- Choisir un site --</option>';
                
                // Ajouter les options
                result.data.forEach(site => {
                    const option = document.createElement('option');
                    option.value = site.id;
                    option.textContent = `${site.code} - ${site.libelle}`;
                    siteSelect.appendChild(option);
                });
                
                // Réactiver le select
                siteSelect.disabled = false;
            } else {
                siteSelect.innerHTML = '<option value="">Aucun site disponible</option>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des sites:', error);
            siteSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        } finally {
            siteSelect.disabled = false;
        }
    }

    async calculateIndemnities() {
        const typeMission = document.querySelector('#dom_form2_sousTypeDocument')?.value;
        const categorie = document.querySelector('#dom_form2_categorie')?.value;
        const site = document.querySelector('#dom_form2_site')?.value;
        const nombreJours = document.querySelector('#dom_form2_nombreJour')?.value || 1;

        if (!typeMission || !categorie || !site) return;

        try {
            const response = await fetch(`${this.apiBaseUrl}/calculate-indemnities`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    typeMissionId: typeMission,
                    categorieId: categorie,
                    siteId: site,
                    nombreJours: parseInt(nombreJours)
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.updateIndemnityFields(result.data);
            }
        } catch (error) {
            console.error('Erreur lors du calcul des indemnités:', error);
        }
    }

    async loadEmployeeInfo(matricule) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/employee-info/${matricule}`);
            const result = await response.json();
            
            if (result.success) {
                this.populateEmployeeFields(result.data);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des informations employé:', error);
        }
    }

    async loadStep1Data() {
        try {
            const response = await fetch('/api/dom/form1-data');
            const data = await response.json();
            
            if (data.success) {
                this.formData = data.data;
                this.applyStep1Data();
            }
        } catch (error) {
            console.error('Erreur lors du chargement des données étape 1:', error);
        }
    }

    populateEmployeeFields(employeeData) {
        const nomInput = document.querySelector('#dom_form2_nom');
        const prenomInput = document.querySelector('#dom_form2_prenom');
        
        if (nomInput) nomInput.value = employeeData.nom || '';
        if (prenomInput) prenomInput.value = employeeData.prenom || '';
    }

    updateIndemnityFields(data) {
        const indemniteForfaitaireInput = document.querySelector('#dom_form2_indemniteForfaitaire');
        const totalForfaitaireInput = document.querySelector('#dom_form2_totalIndemniteForfaitaire');
        
        if (indemniteForfaitaireInput) {
            indemniteForfaitaireInput.value = data.montant_base || '';
        }
        
        if (totalForfaitaireInput) {
            totalForfaitaireInput.value = data.total_forfaitaire || '';
        }

        // Recalculer le total général
        this.calculateTotalGeneral();
    }

    calculateTotalGeneral() {
        let total = 0;
        
        // Indemnité de déplacement
        const indemniteDeplacement = document.querySelector('#dom_form2_totalIndemniteDeplacement')?.value;
        if (indemniteDeplacement) {
            total += parseFloat(indemniteDeplacement.replace(/[^\d]/g, '')) || 0;
        }
        
        // Indemnité forfaitaire
        const totalForfaitaire = document.querySelector('#dom_form2_totalIndemniteForfaitaire')?.value;
        if (totalForfaitaire) {
            total += parseFloat(totalForfaitaire.replace(/[^\d]/g, '')) || 0;
        }
        
        // Autres dépenses
        const autresDepenses = document.querySelector('#dom_form2_totalAutresDepenses')?.value;
        if (autresDepenses) {
            total += parseFloat(autresDepenses.replace(/[^\d]/g, '')) || 0;
        }

        const totalGeneralInput = document.querySelector('#dom_form2_totalGeneralPayer');
        if (totalGeneralInput) {
            totalGeneralInput.value = this.formatNumber(total);
        }
    }

    formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    displayValidationResult(fieldName, result) {
        const field = document.querySelector(`#dom_form2_${fieldName}`);
        if (!field) return;

        // Supprimer les messages précédents
        this.removeValidationMessages(field);

        const messageDiv = document.createElement('div');
        messageDiv.className = `validation-message ${result.valid ? 'valid' : 'invalid'}`;
        messageDiv.textContent = result.message;

        field.parentNode.appendChild(messageDiv);
    }

    removeValidationMessages(field) {
        const existingMessages = field.parentNode.querySelectorAll('.validation-message');
        existingMessages.forEach(msg => msg.remove());
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showWarning(message) {
        this.showNotification(message, 'warning');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    hideWarning() {
        const warningElement = document.querySelector('.notification.warning');
        if (warningElement) {
            warningElement.remove();
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Ajouter au début du formulaire
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(notification, form.firstChild);
            
            // Supprimer après 5 secondes
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }

    async validateForm(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/validate-dom`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const result = await response.json();
            
            if (result.success && result.data.is_valid) {
                form.submit();
            } else {
                this.displayValidationErrors(result.data.errors);
            }
        } catch (error) {
            console.error('Erreur lors de la validation:', error);
            this.showError('Erreur lors de la validation du formulaire');
        }
    }

    displayValidationErrors(errors) {
        errors.forEach(error => {
            this.showError(`${error.field}: ${error.message}`);
        });
    }

    applyStep1Data() {
        // Appliquer les données de l'étape 1 aux champs du formulaire
        Object.keys(this.formData).forEach(key => {
            const field = document.querySelector(`#dom_form2_${key}`);
            if (field && this.formData[key]) {
                field.value = this.formData[key];
            }
        });
    }
}

// Initialiser le gestionnaire DOM quand le document est prêt
document.addEventListener('DOMContentLoaded', () => {
    new DomManager();
});
