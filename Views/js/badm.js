 import { form as formCompleBadm, send , fetchData} from "./badm/formCompleBadm";

// const envoyerBadm = document.form.enregistrer
// formCompleBadm.addEventListener('submit', send);


fetchData();

document.getElementById('agenceDestinataire').addEventListener('change', function() {
    var selectedOption = this.value.toUpperCase();
    fetchData(selectedOption); // Appeler fetchData avec la nouvelle option sélectionnée
});
