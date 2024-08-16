import Validator from "validatorjs";
import { FetchManager } from "./../FetchManager.js";

const fetchManager = new FetchManager("/Hffintranet");
export const form = document.form;
// let dateDemande = form.dateDemande.value;
// let idMateriel = form.idMateriel.value;
//let agenceEmetteur = form.agenceEmetteur.value;
//let serviceEmetteur = form.serviceEmetteur.value;
// let agenceServiceEmetteur = `${agenceEmetteur}-${serviceEmetteur}`;
// let casierEmetteur = form.casierEmetteur.value;
const agenceDestinataire = form.agenceDestinataire;
const serviceDestinataire = form.serviceDestinataire;
//console.log(agenceDestinataire);
//console.log(serviceDestinataire);

//console.log(numBdm);
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

// export function fetchData(selectOption = undefined) {
//   console.log(fetchManager);
//   fetchManager
//     .get("serviceDestinataire")
//     .then((data) => {
//       if (!data) {
//         throw new Error("Données reçues invalides ou vides");
//       }

//       console.log(data);
//       // Assurez-vous que selectOption est défini avant de continuer
//       if (selectOption === undefined && agenceDestinataire) {
//         selectOption = agenceDestinataire.value.toUpperCase();
//       }

//       if (data[selectOption]) {
//         const optionsData = data[selectOption];
//         let optionsHTML = optionsData
//           .map(
//             (option) =>
//               `<option value="${option.toUpperCase()}">${option.toUpperCase()}</option>`
//           )
//           .join("");
//         serviceDestinataire.innerHTML = optionsHTML;
//       } else {
//         throw new Error("Option sélectionnée non trouvée dans les données");
//       }
//     })
//     .catch((error) => {
//       console.error("Erreur lors du traitement des données:", error);
//     });
// }
export function fetchData(selectOption = undefined) {
  //const fetchManager = new FetchManager("/Hffintranet/");
  fetchManager
    .get("serviceDestinataire")
    .then((data) => {
      console.log(data);

      //Sélectionner l'option spécifiée
      if (selectOption === undefined) {
        setTimeout(() => {
          //selectOption = document.getElementById('agenceDestinataire').value.toUpperCase();
          selectOption = agenceDestinataire.value.toUpperCase();
          //console.log(selectOption);
        }, 1000);
      }

      setTimeout(() => {
        //console.log(selectOption);
        //const serviceDestinataire = document.getElementById('serviceDestinataire');
        let taille = data[selectOption].length;
        //console.log(taille);
        let optionsHTML = "";
        for (let i = 0; i < taille; i++) {
          optionsHTML += `<option value="${data[selectOption][
            i
          ].toUpperCase()}">${data[selectOption][i].toUpperCase()}</option>`;
        }
        serviceDestinataire.innerHTML = optionsHTML;
      }, 1000); // Mettre à jour le contenu de serviceIrium une fois que toutes les options ont été ajoutées
    })
    .catch((error) => {
      console.error(error);
    });
}

export function changeService() {
  var selectedOption = this.value.toUpperCase();
  fetchData(selectedOption);
}

export const send = (event) => {
  event.preventDefault();

  let data = {
    motifArretMateriel: motifArretMateriel,
    nomClient: nomClient,
    prixHt: prixHt,
    motifMiseRebut: motifMiseRebut,
  };

  let rules = {
    motifArretMateriel: "required|max:100",
    nomClient: "max:50",
    motifMiseRebut: "max:100",
  };

  let messages = {
    "required.motifArretMateriel": "Le champ email est obligatoire.",
    "max.motifArretMateriel": "caractères maximum: 100",
    "max.nomClient": "caractères maximum: 50",
    "max.motifMiseRebut": "caractères maximum: 100",
  };

  let validation = new Validator(data, rules, messages);

  if (validation.passes()) {
    console.log("Validation avec succes");
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
    console.log("Validation failed");
    const errors = validation.errors.all();
    console.log(errors);

    for (let field in errors) {
      document.querySelector(`#error-${field}`).textContent = errors[field][0]; // Affiche le premier message d'erreur pour chaque champ
    }
  }
};
