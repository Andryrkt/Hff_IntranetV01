import { FetchManager } from "../api/FetchManager";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

document.addEventListener("DOMContentLoaded", (event) => {
  /**
   *  changer le service destinataire et le casier destiantaire
   *  selon l'agence destinataire
   */
  const agenceDebiteurInput = document.querySelector("#badm_form2_agence");
  const serviceDebiteurInput = document.querySelector("#badm_form2_service");
  const casierDestinataireInput = document.querySelector(
    "#badm_form2_casierDestinataire"
  );

  agenceDebiteurInput.addEventListener("change", selectAgence);

  function selectAgence() {
    const agenceDebiteur = agenceDebiteurInput.value;

    //MISE EN PLACE DU SERVICE DESTINATAIRE
    let url = `api/badm/service-fetch/${agenceDebiteur}`;
    fetchManager
      .get(url)
      .then((services) => {
        console.log(services);
        serviceDebiteurInput.disabled = false;

        // Supprimer toutes les options existantes
        while (serviceDebiteurInput.options.length > 0) {
          serviceDebiteurInput.remove(0);
        }

        // Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < services.length; i++) {
          var option = document.createElement("option");
          option.value = services[i].value;
          option.text = services[i].text;
          serviceDebiteurInput.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
          var option = serviceDebiteurInput.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));

    //MISE EN PLACE DU CASIER DESTINATAIRE
    let urlCasier = `api/badm/casier-fetch/${agenceDebiteur}`;
    fetchManager
      .get(urlCasier)
      .then((casiers) => {
        console.log(casiers);

        casierDestinataireInput.disabled = false;

        // Supprimer toutes les options existantes
        while (casierDestinataireInput.options.length > 0) {
          casierDestinataireInput.remove(0);
        }

        // Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < casiers.length; i++) {
          var option = document.createElement("option");
          option.value = casiers[i].value;
          option.text = casiers[i].text;
          casierDestinataireInput.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < casierDestinataireInput.options.length; i++) {
          var option = casierDestinataireInput.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  /**
   * AFFICHAGE des champs image et fichier
   */
  const typeMouvementInput = document.querySelector(
    "#badm_form2_typeMouvement"
  );
  const imageRebutInput = document.querySelector("#badm_form2_nomImage");
  const fichierRebutInput = document.querySelector("#badm_form2_nomFichier");
  if (typeMouvementInput.value !== "5") {
    imageRebutInput.parentElement.style.display = "none";
    fichierRebutInput.parentElement.style.display = "none";
  } else {
    imageRebutInput.parentElement.style.display = "block";
    fichierRebutInput.parentElement.style.display = "block";
  }

  /**
   * CHANGEMENT DE COULEUR SELON LE TYPE DE MOUVEMENT
   */
  function typeDemandeChangementCouleur(typeDemande) {
    const codeMouvement = document.querySelector("#codeMouvement");

    if (typeDemande === "1") {
      codeMouvement.classList.add("codeMouvementParc");
    } else if (typeDemande === "2") {
      codeMouvement.classList.add("codeMouvementAgenceService");
    } else if (typeDemande === "3") {
      codeMouvement.classList.add("codeMouvementCasier");
    } else if (typeDemande === "4") {
      codeMouvement.classList.add("codeMouvementActif");
    } else if (typeDemande === "5") {
      codeMouvement.classList.add("codeMouvementRebut");
    }
  }
  typeDemandeChangementCouleur(typeMouvementInput.value);

  /** RENDRE MAJUSCULE LE DONNER ECRIT */
  const motifMaterielInput = document.querySelector(
    "#badm_form2_motifMateriel"
  );
  const nomClientInput = document.querySelector("#badm_form2_nomClient");
  const motifMiseRebutInput = document.querySelector(
    "#badm_form2_motifMiseRebut"
  );

  motifMaterielInput.addEventListener("input", majuscule);
  nomClientInput.addEventListener("input", majuscule);
  motifMiseRebutInput.addEventListener("input", majuscule);

  function majuscule() {
    this.value = this.value.toUpperCase();

    const maxLength = 100;
    if (this.id === "nomClientInput") {
      maxLength = 50;
    }
    let currentLength = this.value.length;

    if (currentLength > maxLength) {
      this.value = this.value.substring(0, maxLength);
      currentLength = maxLength;
    }

    const charCountId = `charCount${this.id.slice(-1)}`;
    const charCount = document.getElementById(charCountId);
    if (charCount) {
      charCount.textContent = `${currentLength}/${maxLength}`;
    }
  }

  /**
   * SELECTE 2/ permet de faire une recherche sur le select
   */
  $(document).ready(function () {
    $(".selectCasier").select2({
      placeholder: "-- Choisir un casier --",
      allowClear: true,
      theme: "bootstrap",
    });
  });

  /** PERMET DE FORMTER UN NOMBRE (utilisation du bibliothème numeral.js)*/
  // Définir une locale personnalisée
  numeral.register("locale", "fr-custom", {
    delimiters: {
      thousands: ".",
      decimal: ",",
    },
    abbreviations: {
      thousand: "k",
      million: "m",
      billion: "b",
      trillion: "t",
    },
    ordinal: function (number) {
      return number === 1 ? "er" : "ème";
    },
    currency: {
      symbol: "Ar",
    },
  });

  // Utiliser la locale personnalisée
  numeral.locale("fr-custom");

  function formatNumberInt(value) {
    return numeral(value).format(0, 0);
  }

  const prixHtInput = document.querySelector("#badm_form2_prixVenteHt");

  prixHtInput.addEventListener("input", () => {
    prixHtInput.value = formatNumberInt(prixHtInput.value);
  });
});
