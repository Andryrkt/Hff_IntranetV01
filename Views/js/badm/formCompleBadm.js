import { FetchManager } from "./../FetchManager.js";
import Validator from 'validatorjs';

export const form = document.form;
let dateDemande = form.dateDemande.value;
let idMateriel = form.idMateriel.value;
let agenceEmetteur = form.agenceEmetteur.value.split(' ')[0];
let serviceEmetteur = form.serviceEmetteur.value.split(' ')[0];
let agenceServiceEmetteur = `${agenceEmetteur}-${serviceEmetteur}`;
let casierEmetteur = form.casierEmetteur.value;
let agenceDestinataire = form.agenceDestinataire.value.split(' ')[0];
let serviceDestinataire = form.serviceDestinataire.value.split(' ')[0];
let agenceServiceDestinataire = `${agenceDestinataire}-${serviceDestinataire}`;
let casierDestinataire = form.casierDestinataire.value;
let motifArretMateriel = form.motifArretMateriel.value;
let etatAchat = form.etatAchat.value;
let dateMiseLocation = form.dateMiseLocation.value;
let coutAcquisition = form.coutAcquisition.value;
let amortissement = form.amortissement.value;
let valeurNetComptable = form.valeurNetComptable.value;
let nomClient = form.nomClient.value;
let modalitePaiement = form.modalitePaiement.value;
let prixHt = form.prixHt.value;
let motifMiseRebut = form.motifMiseRebut.value;
let heuresMachine = form.heuresMachine.value;
let kilometrage = form.kilometrage.value;


const fetchManager = new FetchManager('http://localhost');

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
        const dataToPost = {
            Date_Demande: dateDemande,
            ID_Materiel: idMateriel,
            Agence_Service_Emetteur:  agenceServiceEmetteur,
            Casier_Emetteur: casierEmetteur,
            Agence_Service_Destinataire: agenceServiceDestinataire,
            Casier_Destinataire: casierDestinataire,
            Motif_Arret_Materiel: motifArretMateriel,
            Etat_Achat: etatAchat,
            Date_Mise_Location: dateMiseLocation,
            Cout_Acquisition: coutAcquisition,
            Amortissement: amortissement,
            Valeur_Net_Comptable: valeurNetComptable,
            Nom_Client : nomClient,
            Modalite_Paiement : modalitePaiement,
            Prix_Vente_HT : prixHt,
            Motif_Mise_Rebut : motifMiseRebut,
            Heure_machine : heuresMachine,
            KM_machine : kilometrage
        };
        console.log(dataToPost);
        fetchManager.post('list', dataToPost)
        .then(data => console.log(data))
        .catch(error => console.error(error));
    } else {
        console.log('Validation failed');
        const errors = validation.errors.all();
        console.log(errors);
        
        for (let field in errors) {
            document.querySelector(`#error-${field}`).textContent = errors[field][0]; // Affiche le premier message d'erreur pour chaque champ
        }


        // for (let field in errors) {
        //     const listeErreurs = document.createElement('ul'); // Création d'une liste pour les erreurs
        
        //     errors[field].forEach((error) => {
        //         const itemErreur = document.createElement('li'); // Création d'un élément de liste pour chaque erreur
        //         itemErreur.textContent = error;
        //         listeErreurs.appendChild(itemErreur); // Ajout de l'erreur à la liste
        //     });
        
        //     // Sélectionner l'emplacement d'affichage des erreurs et vider les erreurs précédentes
        //     const emplacementErreur = document.querySelector(`#error-${field}`);
        //     emplacementErreur.innerHTML = ''; // Nettoyer les messages d'erreur précédents
        //     emplacementErreur.appendChild(listeErreurs); // Ajouter la nouvelle liste d'erreurs
        // }
        
    }

};