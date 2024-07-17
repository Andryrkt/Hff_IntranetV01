document.addEventListener("DOMContentLoaded", (event) => {
  /**
   * N'AFFICHE PAS LES CHAMPS matricule et cin selon le statut de la salarier
   */
  const cinInput = document.querySelector("#dom_form2_cin");
  const matriculeInput = document.querySelector("#dom_form2_matricule");

  function form1Data() {
    let url = `/Hffintranet/form1Data-fetch`;
    fetch(url)
      .then((response) => response.json())
      .then((form1Data) => {
        console.log(form1Data);
        if (form1Data.salarier === "PERMANENT") {
          cinInput.parentElement.style.display = "none";
        } else {
          matriculeInput.parentElement.style.display = "none";
        }
      })
      .catch((error) => console.error("Error:", error));
  }
  form1Data();

  /**
   * recuperer l'agence debiteur et changer le service debiteur selon l'agence
   */
  const agenceDebiteurInput = document.querySelector("#dom_form2_agence");
  const serviceDebiteurInput = document.querySelector("#dom_form2_service");
  agenceDebiteurInput.addEventListener("change", selectAgence);

  function selectAgence() {
    const agenceDebiteur = agenceDebiteurInput.value;
    let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
    fetch(url)
      .then((response) => response.json())
      .then((services) => {
        console.log(services);

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
  }

  /**
   * CALCULE et AFFICHAGE DU NOMBRE DE JOUR
   */
  const dateDebutInput = document.querySelector("#dom_form2_dateDebut");
  const dateFinInput = document.querySelector("#dom_form2_dateFin");
  const nombreDeJourInput = document.querySelector("#dom_form2_nombreJour");

  const errorMessage = document.createElement("div");
  errorMessage.style.color = "red";
  errorMessage.style.display = "none";

  if (dateDebutInput && dateFinInput && nombreDeJourInput) {
    dateDebutInput.addEventListener("change", calculateDays);
    dateFinInput.addEventListener("change", calculateDays);
    dateFinInput.parentNode.insertBefore(
      errorMessage,
      dateFinInput.nextSibling
    );
  }

  function calculateDays() {
    const dateDebutValue = dateDebutInput.value;
    const dateFinValue = dateFinInput.value;

    if (dateDebutValue && dateFinValue) {
      const dateDebut = new Date(dateDebutValue);
      const dateFin = new Date(dateFinValue);

      if (dateDebut > dateFin) {
        errorMessage.textContent =
          "La date de début ne peut pas être supérieure à la date de fin.";
        errorMessage.style.display = "block";
        nombreDeJourInput.value = "";
      } else {
        errorMessage.style.display = "none";
        const timeDifference = dateFin - dateDebut;
        const dayDifference = timeDifference / (1000 * 3600 * 24);
        nombreDeJourInput.value = dayDifference;
        updateTotalIndemnity();
      }
    }
  }

  /**
   * CALCULE et AFFICHAGE total indemnité de déplacement
   */
  const totalIdemniteDeplacementInput = document.querySelector(
    "#dom_form2_totalIndemniteDeplacement"
  );
  const idemnityDeplInput = document.querySelector("#dom_form2_idemnityDepl");

  function updateTotalIndemnity() {
    const nombreDeJour = parseInt(nombreDeJourInput.value);
    const indemnityDepl = parseInt(
      idemnityDeplInput.value.replace(/[^\d]/g, "")
    );

    if (!isNaN(nombreDeJour) && !isNaN(indemnityDepl)) {
      const totalIndemnity = nombreDeJour * indemnityDepl;

      totalIdemniteDeplacementInput.value = formatNumberInt(totalIndemnity);
    } else {
      totalIdemniteDeplacementInput.value = "";
    }
  }

  if (idemnityDeplInput) {
    idemnityDeplInput.addEventListener("input", () => {
      idemnityDeplInput.value = formatNumberInt(idemnityDeplInput.value);
      updateTotalIndemnity();
    });
  }

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

  /** AFFICHAGE DE l'INDEMNITE FORFAITAIRE JOURNALIERE selon le site */
  const indemniteForfaitaireJournaliereInput = document.querySelector(
    "#dom_form2_indemniteForfaitaire"
  );
  const siteInput = document.querySelector("#dom_form2_site");

  siteInput.addEventListener("change", indemnitySite);

  function indemnitySite() {
    const siteValue = siteInput.value;
    console.log(siteValue);
  }
});
