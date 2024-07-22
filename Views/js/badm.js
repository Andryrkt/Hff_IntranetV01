import {
  form as formCompleBadm,
  send,
  fetchData,
  changeService,
} from "./badm/formCompleBadm";
// import { FetchManager } from "./FetchManager.js";
import { fetchCasier, changeCasier } from "./badm/formCompleCasierBadm";
import {
  formatNumber,
  verifierTailleEtType,
  envoieformulaire,
  typeDemandeChangementCouleur,
} from "./badm/formDomBadm";

document.addEventListener("DOMContentLoaded", (event) => {
  const typeDemande = formCompleBadm.codeMouvement.value;
  const agenceDestinataire = formCompleBadm.agenceDestinataire;
  const serviceDestinataire = formCompleBadm.serviceDestinataire;
  const motifArretMateriel = formCompleBadm.motifArretMateriel;
  const agenceEmetteur = formCompleBadm.agenceEmetteur;
  const casierDestinataire = formCompleBadm.casierDestinataire;
  const motifMiseRebut = formCompleBadm.motifMiseRebut;
  const casierEmetteur = formCompleBadm.casierEmetteur;
  const prixHt = formCompleBadm.prixHt;
  const button = formCompleBadm.badmComplet;
  const agenceDestinataireDetail = formCompleBadm.agenceDestinataireDetail;
  const casierDestinataireDetail = formCompleBadm.casierDestinataireDetail;

  // console.log(agenceDestinataireDetail.value, casierDestinataireDetail.value);
  // console.log(document.querySelector( `#agenceDestinataire option`).value);
  //const envoyerBadm = document.form.enregistrer
  // formCompleBadm.addEventListener('submit', send);
  console.log(formCompleBadm.numBdm.value);
  if (formCompleBadm.numBdm.value === "") {
    fetchData();

    console.log("oui");
    document
      .getElementById("agenceDestinataire")
      .addEventListener("change", changeService);
    fetchCasier();

    document
      .getElementById("agenceDestinataire")
      .addEventListener("change", changeCasier);

    if (typeDemande === "CHANGEMENT DE CASIER") {
      setTimeout(() => {
        document.querySelector(
          `#agenceDestinataire option[value="${document
            .querySelector("#agenceEmetteur")
            .value.toUpperCase()}"]`
        ).selected = true;
      }, 1000);

      setTimeout(() => {
        document.querySelector(
          `#serviceDestinataire option[value="${document
            .querySelector("#serviceEmetteur")
            .value.toUpperCase()}"]`
        ).selected = true;
      }, 5000);

      agenceDestinataire.disabled = true;
      serviceDestinataire.disabled = true;
      //console.log(agenceDestinataire, serviceDestinataire);
    }

    if (typeDemande === "CESSION D'ACTIF") {
      const nombres = ["90", "91", "92"];
      let condition = nombres.includes(agenceEmetteur.value.split(" ")[0]);
      // console.log(agenceEmetteur.value.split(" ")[0]);
      // console.log(condition);
      if (condition) {
        setTimeout(() => {
          console.log(document.querySelector(`#agenceDestinataire`));
          document.querySelector(
            `#agenceDestinataire option[value="90 COMM ENERGIE"]`
          ).selected = true;
        }, 1000);
        setTimeout(() => {
          document.querySelector(
            `#serviceDestinataire option[value='COM COMMERCIAL']`
          ).selected = true;
        }, 5000);
        setTimeout(() => {
          document.querySelector(`#casierDestinataire`).value = "";
        }, 5000);
      } else {
        setTimeout(() => {
          document.querySelector(
            `#agenceDestinataire option[value="01 ANTANANARIVO"]`
          ).selected = true;
        }, 1000);
        setTimeout(() => {
          document.querySelector(
            `#serviceDestinataire option[value='COM COMMERCIAL']`
          ).selected = true;
        }, 5000);
        setTimeout(() => {
          document.querySelector(`#casierDestinataire`).value = "";
        }, 5000);
      }

      agenceDestinataire.disabled = true;
      serviceDestinataire.disabled = true;
      motifArretMateriel.disabled = true;
      motifMiseRebut.disabled = true;
      casierDestinataire.disabled = true;
    }

    if (typeDemande === "MISE AU REBUT") {
      setTimeout(() => {
        document.querySelector(
          `#agenceDestinataire option[value="${document
            .querySelector("#agenceEmetteur")
            .value.toUpperCase()}"]`
        ).selected = true;
      }, 1000);

      setTimeout(() => {
        console.log(
          document.querySelector("#serviceEmetteur").value.toUpperCase().trim()
        );
        //console.log(document.querySelector(`#serviceDestinataire option[value='COM COMMERCIAL']`));
        document.querySelector(
          `#serviceDestinataire option[value="${document
            .querySelector("#serviceEmetteur")
            .value.toUpperCase()
            .trim()}"]`
        ).selected = true;
      }, 5000);

      setTimeout(() => {
        document.querySelector(
          `#casierDestinataire option[value="${casierEmetteur.value}"]`
        ).selected = true;
      }, 5000);

      form.nomClient.disabled = true;
      form.modalitePaiement.disabled = true;
      form.prixHt.disabled = true;
      agenceDestinataire.disabled = true;
      serviceDestinataire.disabled = true;
      motifArretMateriel.disabled = true;
      casierDestinataire.disabled = true;

      /**
       * filtre le taille de l'image entrer par l'utilisateur
       */
      formCompleBadm.imageRebut.addEventListener(
        "change",
        verifierTailleEtType
      );
    }
  } else {
    console.log("non");
    fetchData();
    fetchCasier();

    setTimeout(() => {
      document.querySelector(
        `#serviceDestinataire option[value='${agenceDestinataireDetail.value}']`
      ).selected = true;
    }, 5000);
    setTimeout(() => {
      document.querySelector(
        `#casierDestinataire option[value='${casierDestinataireDetail.value}']`
      ).selected = true;
    }, 5000);
  }

  /**
   * ecouter sur le button et affiche une verification
   */

  // if (button !== undefined) {
  //   button.addEventListener("click", envoieformulaire);
  // }

  /**
   * changement de coueleur type de mouvemnt
   */
  typeDemandeChangementCouleur(typeDemande);

  /**
   * formater le prix
   */
  prixHt.addEventListener("input", formatNumber);
});
