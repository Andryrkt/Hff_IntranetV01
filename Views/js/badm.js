 import { form as formCompleBadm, send , fetchData, fetchCasier, typeDemandeChangementCouleur, formatNumber, verifierTailleEtType} from "./badm/formCompleBadm";
 //import { FetchManager } from "./FetchManager.js";
const typeDemande = formCompleBadm.codeMouvement.value
const agenceDestinataire = formCompleBadm.agenceDestinataire;
const serviceDestinataire = formCompleBadm.serviceDestinataire;
const motifArretMateriel = formCompleBadm.motifArretMateriel;
const agenceEmetteur = formCompleBadm.agenceEmetteur;
//const envoyerBadm = document.form.enregistrer
// formCompleBadm.addEventListener('submit', send);


fetchData();

document.getElementById('agenceDestinataire').addEventListener('change', function() {
    var selectedOption = this.value.toUpperCase();
    fetchData(selectedOption); 
});

if (typeDemande === 'CHANGEMENT DE CASIER') {
    setTimeout(() => {
        document.querySelector(`#agenceDestinataire option[value="${document.querySelector('#agenceEmetteur').value.toUpperCase()}"]`).selected = true;
    }, 300);
    
    setTimeout(() => {
        document.querySelector(`#serviceDestinataire option[value="${document.querySelector('#serviceEmetteur').value.toUpperCase()}"]`).selected = true;
    }, 1000);


    agenceDestinataire.disabled = true;
    serviceDestinataire.disabled = true;
    motifArretMateriel.disabled = true;
    //console.log(agenceDestinataire, serviceDestinataire);

  
}



if(typeDemande === 'CESSION D\'ACTIF'){
    const nombres = ['90', '91', '92'];
    let condition = nombres.includes(agenceEmetteur.value.split(' ')[0]);
    console.log(agenceEmetteur.value.split(' ')[0]);
    console.log(condition);
    if(condition){
        setTimeout(() => {
            document.querySelector(`#agenceDestinataire option[value="90 COMM.ENERGIE"]`).selected = true;
        }, 300);
        setTimeout(() => {
            document.querySelector(`#serviceDestinataire option[value='COM COMMERCIAL']`).selected = true;
        }, 1000);
    } else {
        setTimeout(() => {
            document.querySelector(`#agenceDestinataire option[value="01 ANTANANARIVO"]`).selected = true;
        }, 300);
        setTimeout(() => {
            document.querySelector(`#serviceDestinataire option[value='COM COMMERCIAL']`).selected = true;
        }, 1000);
    }
    agenceDestinataire.disabled = true;
    serviceDestinataire.disabled = true;
    motifArretMateriel.disabled = true;
}


if(typeDemande === 'MISE AU REBUT'){

    setTimeout(() => {
        document.querySelector(`#agenceDestinataire option[value="${document.querySelector('#agenceEmetteur').value.toUpperCase()}"]`).selected = true;
    }, 300);
    
    setTimeout(() => {
        console.log(document.querySelector('#serviceEmetteur').value.toUpperCase().trim());
        //console.log(document.querySelector(`#serviceDestinataire option[value='COM COMMERCIAL']`));
        document.querySelector(`#serviceDestinataire option[value="${document.querySelector('#serviceEmetteur').value.toUpperCase().trim()}"]`).selected = true;
    }, 1000);

    form.nomClient.disabled =true;
    form.modalitePaiement.disabled = true;
    form.prixHt.disabled = true;
    agenceDestinataire.disabled = true;
    serviceDestinataire.disabled = true;
    motifArretMateriel.disabled = true;
}

fetchCasier();

document.getElementById('agenceDestinataire').addEventListener('change', function() {
    var selectedOption = this.value.toUpperCase();
    fetchCasier(selectedOption); 
});


/**
 * ecouter sur le button et affiche une verification
 */

//console.log(formCompleBadm.badmComplet);
formCompleBadm.badmComplet.addEventListener('click', (e) => {

    alert("Veuillez vérifier attentivement avant d'envoyer.");
    // e.preventDefault(); 
    // Swal.fire({
    //     title: "Vous confirmez ?",
    //     text: "Veuillez vérifier attentivement avant d'envoyer.",
    //     icon: "warning",
    //     showCancelButton: true,
    //     confirmButtonColor: "#3085d6",
    //     cancelButtonColor: "#d33",
    //     confirmButtonText: "Oui"
    // }).then((result) => {
    //     if (result.isConfirmed) {
    //         Swal.fire({
    //             title: "Envoyer!",
    //             text: "Votre demande a été bien enregistrée",
    //             icon: "success"
    //         }).then(() => {
               
    //             formCompleBadm.submit();
    //         });
    //     }
    // });
});


/**
 * changement de coueleur type de mouvemnt
 */

typeDemandeChangementCouleur(typeDemande);


/**
 * filtre le taille de l'image entrer par l'utilisateur
 */

formCompleBadm.imageRebut.addEventListener('change', verifierTailleEtType)

/**
 * formater le prix
 */
formCompleBadm.prixHt.addEventListener('input', formatNumber)

