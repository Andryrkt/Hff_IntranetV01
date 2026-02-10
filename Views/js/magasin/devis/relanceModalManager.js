import { FetchManager } from "../../api/FetchManager.js";

document.addEventListener('DOMContentLoaded', function() {
    const relanceModal = document.getElementById('relance');
    const modalRelanceBody = document.getElementById('modalRelanceBody');
    const fetchManager = new FetchManager();

    if (relanceModal) {
        relanceModal.addEventListener('show.bs.modal', async function (event) {
            // Efface le contenu précédent du modal
            modalRelanceBody.innerHTML = '<tr><td colspan="6" class="text-center">Chargement des relances...</td></tr>';

            const button = event.relatedTarget; // Bouton qui a déclenché le modal
            const numeroDevis = button.getAttribute('data-id');

            if (numeroDevis) {
                try {
                    // Utilisation de FetchManager pour l'appel API
                    const relances = await fetchManager.get(`api/devis/${numeroDevis}/relances`);

                    modalRelanceBody.innerHTML = ''; // Vide le message de chargement

                    if (relances.length > 0) {
                        relances.forEach(item => {
                            const row = `
                                <tr>
                                    <td class="text-center">${item.numeroDevis}</td>
                                    <td class="text-center">${item.numeroRelance}</td>
                                    <td class="text-center">${item.dateRelance ? new Date(item.dateRelance).toLocaleDateString('fr-FR') : ''}</td>
                                    <td class="text-center">${item.societe}</td>
                                    <td class="text-center">${item.agence}</td>
                                    <td class="text-center">${item.utilisateur}</td>
                                </tr>
                            `;
                            modalRelanceBody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        modalRelanceBody.innerHTML = '<tr><td colspan="6" class="text-center">Aucune relance trouvée pour ce devis.</td></tr>';
                    }

                } catch (error) {
                    console.error('Erreur lors du chargement des relances:', error);
                    modalRelanceBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des relances.</td></tr>';
                }
            } else {
                modalRelanceBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Numéro de devis non spécifié.</td></tr>';
            }
        });
    }
});
