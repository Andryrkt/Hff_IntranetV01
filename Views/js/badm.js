 import { form as formCompleBadm, send , fetchData, fetchCasier} from "./badm/formCompleBadm";
 import { FetchManager } from "./FetchManager.js";
//const envoyerBadm = document.form.enregistrer
// formCompleBadm.addEventListener('submit', send);


fetchData();

document.getElementById('agenceDestinataire').addEventListener('change', function() {
    var selectedOption = this.value.toUpperCase();
    fetchData(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
});

fetchCasier();

document.getElementById('agenceDestinataire').addEventListener('change', function() {
    var selectedOption = this.value.toUpperCase();
    fetchCasier(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
});


formCompleBadm.badmComplet.addEventListener('submit', (e) => {
    e.preventDefault(); // Empêcher la soumission du formulaire immédiatement
    Swal.fire({
        title: "Vous confirmez ?",
        text: "Veuillez vérifier attentivement avant d'envoyer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Envoyer!",
                text: "Votre demande a été bien enregistrée",
                icon: "success"
            }).then(() => {
                // Soumettre le formulaire manuellement après confirmation
                formCompleBadm.badmComplet.submit();
            });
        }
    });
});




