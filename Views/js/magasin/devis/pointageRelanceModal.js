import { FetchManager } from '../../api/FetchManager.js';

document.addEventListener('DOMContentLoaded', function() {
    const fetchManager = new FetchManager();
    var pointageRelanceModal = document.getElementById('pointageRelanceModal');

    if (pointageRelanceModal) {
        pointageRelanceModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var numeroDevis = button.getAttribute('data-bs-numero-devis');

            var modalBody = pointageRelanceModal.querySelector('.modal-body');
            modalBody.innerHTML = 'Chargement du formulaire...';

            const endpoint = `magasin/dematerialisation/pointage-relance-form/${numeroDevis}`;

            fetchManager.get(endpoint, 'text')
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading the form:', error);
                    modalBody.innerHTML = '<p class="text-danger">Erreur lors du chargement du formulaire.</p>';
                });
        });
    }

    document.body.addEventListener('submit', function(event) {
        if (event.target && event.target.id === 'pointageRelanceForm') {
            event.preventDefault();

            var form = event.target;
            var formData = new FormData(form);
            var data = Object.fromEntries(formData.entries()); // Convert FormData to plain object

            var modal = bootstrap.Modal.getInstance(document.getElementById('pointageRelanceModal'));
            
            const submitEndpoint = 'magasin/dematerialisation/pointage-relance-submit';

            fetchManager.post(submitEndpoint, data)
                .then(response => {
                    if (response.success) {
                        modal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur de validation',
                            text: response.message,
                            footer: response.errors ? `<pre style="text-align: left;">${response.errors}</pre>` : ''
                        });
                    }
                })
                .catch(error => {
                    console.error('Error submitting the form:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Une erreur inattendue est survenue lors de la soumission du formulaire.',
                    });
                });
        }
    });
});
