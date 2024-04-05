// import { form as formCompleBadm, send } from "./badm/formCompleBadm";
import { FetchManager } from "./FetchManager.js";
import Validator from 'validatorjs';

 const form = document.form;
let dateDemande = form.dateDemande.value;
let idMateriel = form.idMateriel.value;
let agenceEmetteur = form.agenceEmetteur.value.split(' ')[0];
let serviceEmetteur = form.serviceEmetteur.value.split(' ')[0];
let agenceServiceEmetteur = `${agenceEmetteur}-${serviceEmetteur}`;
let casierEmetteur = form.casierEmetteur.value;
let agenceDestinataire = form.agenceDestinataire.value.split(' ')[0];
let serviceDestinataire = form.serviceDestinataire.value.split(' ')[0];
let agenceServiceDestinataire = `${agenceDestinataire}-${serviceDestinataire}`;
let casierDestinataireAgence = form.casierDestinataireAgence.value || '';
let casierDestinataireChantier = form.casierDestinataireChantier.value || '';
let casierDestinataireStd = form.casierDestinataireStd.value || '';
let casierDestinataire = `${casierDestinataireAgence} ${casierDestinataireChantier} ${casierDestinataireStd}`;
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


const fetchManager = new FetchManager('/Hffintranet/');

const send =  (event) => {
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
        fetchManager.post('index.php?action=envoiFormCompleBadm', dataToPost)
        .then(data => console.log(data))
        .catch(error => console.error(error));
    } else {
        console.log('Validation failed');
        const errors = validation.errors.all();
        console.log(errors);
        
        for (let field in errors) {
            document.querySelector(`#error-${field}`).textContent = errors[field][0]; // Affiche le premier message d'erreur pour chaque champ
        }
    }

};
//const envoyerBadm = document.form.enregistrer
//form.addEventListener('submit', send);