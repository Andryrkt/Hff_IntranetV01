
/**
 * Initialise la fonctionnalité "Tout sélectionner" pour un groupe de cases à cocher.
 * La structure HTML attendue est :
 * <div data-checkbox-group>
 *   <input type="checkbox" data-checkbox-group-all>
 *   <div data-checkbox-group-items>
 *     <!-- Les cases à cocher individuelles -->
 *     <input type="checkbox">
 *     ...
 *   </div>
 * </div>
 */
function initializeCheckboxGroup(groupElement) {
    const selectAllCheckbox = groupElement.querySelector('[data-checkbox-group-all]');
    const itemsContainer = groupElement.querySelector('[data-checkbox-group-items]');
    
    if (!selectAllCheckbox || !itemsContainer) {
        console.error('Le groupe de cases à cocher est mal configuré. Il manque un élément "select-all" ou "items".', groupElement);
        return;
    }

    const individualCheckboxes = itemsContainer.querySelectorAll('input[type="checkbox"]');

    function updateSelectAllState() {
        const allChecked = Array.from(individualCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(individualCheckboxes).some(checkbox => checkbox.checked);
        
        selectAllCheckbox.checked = allChecked;
        // Gère l'état indéterminé si seulement quelques cases sont cochées
        if (!allChecked && someChecked) {
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.indeterminate = false;
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        individualCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    individualCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    // État initial au chargement
    updateSelectAllState();
}

// Initialise tous les groupes de cases à cocher sur la page
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-checkbox-group]').forEach(initializeCheckboxGroup);
});
