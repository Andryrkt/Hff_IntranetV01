import { FetchManager } from "./../FetchManager.js";
import Validator from 'validatorjs';

export const form = document.form;
// let dateDemande = form.dateDemande.value;
// let idMateriel = form.idMateriel.value;
// let agenceEmetteur = form.agenceEmetteur.value.split(' ')[0];
// let serviceEmetteur = form.serviceEmetteur.value.split(' ')[0];
// let agenceServiceEmetteur = `${agenceEmetteur}-${serviceEmetteur}`;
// let casierEmetteur = form.casierEmetteur.value;
// let agenceDestinataire = form.agenceDestinataire.value.split(' ')[0];
// let serviceDestinataire = form.serviceDestinataire.value.split(' ')[0];
// let agenceServiceDestinataire = `${agenceDestinataire}-${serviceDestinataire}`;
// let motifArretMateriel = form.motifArretMateriel.value;
// let etatAchat = form.etatAchat.value;
// let dateMiseLocation = form.dateMiseLocation.value;
// let coutAcquisition = form.coutAcquisition.value;
// let amortissement = form.amortissement.value;
// let valeurNetComptable = form.valeurNetComptable.value;
// let nomClient = form.nomClient.value;
// let modalitePaiement = form.modalitePaiement.value;
// let prixHt = form.prixHt.value;
// let motifMiseRebut = form.motifMiseRebut.value;
// let heuresMachine = form.heuresMachine.value;
// let kilometrage = form.kilometrage.value;


const fetchManager = new FetchManager('/Hffintranet/');

export const send =  (event) => {
    event.preventDefault();
    
    let data = {
        motifArretMateriel: motifArretMateriel,
        nomClient: nomClient,
        prixHt: prixHt,
        motifMiseRebut: motifMiseRebut
    };
    
    let rules = {
        motifArretMateriel: 'required|max:100',
        nomClient: 'max:50',
        motifMiseRebut: 'max:100' 
    };
    
    let messages = {
        'required.motifArretMateriel': 'Le champ email est obligatoire.',
        'max.motifArretMateriel': 'caractères maximum: 100',
        'max.nomClient': 'caractères maximum: 50',
        'max.motifMiseRebut': 'caractères maximum: 100'
    };

    let validation = new Validator(data, rules, messages);
    
    if (validation.passes()) {
        console.log('Validation avec succes');
        // const dataToPost = {
        //     Date_Demande: dateDemande,
        //     ID_Materiel: idMateriel,
        //     Agence_Service_Emetteur:  agenceServiceEmetteur,
        //     Casier_Emetteur: casierEmetteur,
        //     Agence_Service_Destinataire: agenceServiceDestinataire,
        //     Casier_Destinataire: casierDestinataire,
        //     Motif_Arret_Materiel: motifArretMateriel,
        //     Etat_Achat: etatAchat,
        //     Date_Mise_Location: dateMiseLocation,
        //     Cout_Acquisition: coutAcquisition,
        //     Amortissement: amortissement,
        //     Valeur_Net_Comptable: valeurNetComptable,
        //     Nom_Client : nomClient,
        //     Modalite_Paiement : modalitePaiement,
        //     Prix_Vente_HT : prixHt,
        //     Motif_Mise_Rebut : motifMiseRebut,
        //     Heure_machine : heuresMachine,
        //     KM_machine : kilometrage
        // };
        // console.log(dataToPost);
        // fetchManager.post('index.php?action=envoiFormCompleBadm', dataToPost)
        // .then(data => console.log(data))
        // .catch(error => console.error(error));
    } else {
        console.log('Validation failed');
        const errors = validation.errors.all();
        console.log(errors);
        
        for (let field in errors) {
            document.querySelector(`#error-${field}`).textContent = errors[field][0]; // Affiche le premier message d'erreur pour chaque champ
        }
    }

};





export function fetchData(selectOption = undefined) {
    const url = "/Hffintranet/index.php?action=anaranaaction";
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur de réseau');
            }
            return response.json();
        })
        .then(data => {

            console.log(data);

           
            //Sélectionner l'option spécifiée
            if (selectOption === undefined) {
                setTimeout(() => {
                    selectOption = document.getElementById('agenceDestinataire').value.toUpperCase();
                    console.log(selectOption);
                }, 300);
            }


            setTimeout(() => {
                console.log(selectOption);
                const serviceDestinataire = document.getElementById('serviceDestinataire');
                let taille = data[selectOption].length;
                console.log(taille);
                let optionsHTML = ''; // Chaîne pour stocker les options HTML
                for (let i = 0; i < taille; i++) {
                    optionsHTML += `<option value="${data[selectOption][i].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
                }
                serviceDestinataire.innerHTML = optionsHTML;
            }, 300); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
        })
        .catch(error => {
            console.error(error);
        });
}



export function fetchCasier(selectOption = undefined)
{
    const fetchManager = new FetchManager('/Hffintranet/');
fetchManager.get('index.php?action=casierDestinataire')
.then(data => 
    {
        console.log(data);
  //Sélectionner l'option spécifiée
  if (selectOption === undefined) {
    setTimeout(() => {
        selectOption = document.getElementById('agenceDestinataire').value.toUpperCase();
        console.log(selectOption);
    }, 300);
}


setTimeout(() => {
    //console.log(selectOption);
    //console.log('okey');
    const casierDestinataire = document.getElementById('casierDestinataire');
    let taille = data[selectOption].length;
    //console.log(taille);
    let optionsHTML = ''; // Chaîne pour stocker les options HTML
    for (let i = 0; i < taille; i++) {
        optionsHTML += `<option value="${data[selectOption][i].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
    }
    casierDestinataire.innerHTML = optionsHTML;
}, 300);

})
.catch(error => console.error(error));
}

/*
changement de couleur pour le code de mouvemnet ou type de demande

*/
export function typeDemandeChangementCouleur(typeDemande){


    const codeMouvement = document.querySelector('#codeMouvement')
    
    if (typeDemande === 'ENTREE EN PARC') {
        codeMouvement.classList.add('codeMouvementParc')
    } else if (typeDemande === 'CHANGEMENT AGENCE/SERVICE') {
        codeMouvement.classList.add('codeMouvementAgenceService')
    } else if(typeDemande === 'CHANGEMENT DE CASIER') {
        codeMouvement.classList.add('codeMouvementCasier')
    } else if(typeDemande === 'CESSION D\'ACTIF') {
        codeMouvement.classList.add('codeMouvementActif')
    } else if(typeDemande === 'MISE AU REBUT') {
        codeMouvement.classList.add('codeMouvementRebut')
    }
    }

