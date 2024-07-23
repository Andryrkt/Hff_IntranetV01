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
        nombreDeJourInput.value = dayDifference + 1;

        updateTotalIndemnity();

        //ajout d'une nouvelle evenement qui sera utiliser en bas
        const event = new Event("valueAdded");
        nombreDeJourInput.dispatchEvent(event);
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

      const event = new Event("valueAdded");
      totalIdemniteDeplacementInput.dispatchEvent(event);
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
    let url = `/Hffintranet/site-idemnite-fetch/${siteValue}`;
    fetch(url)
      .then((response) => response.json())
      .then((indemnite) => {
        console.log(indemnite);
        indemniteForfaitaireJournaliereInput.value = indemnite.montant;
      })
      .catch((error) => console.error("Error:", error));
  }

  /** CALCULE DU TOTAL INDEMNITE FORFAITAIRE */
  const supplementJournalierInput = document.querySelector(
    "#dom_form2_supplementJournaliere"
  );
  const totalindemniteForfaitaireInput = document.querySelector(
    "#dom_form2_totalIndemniteForfaitaire"
  );

  nombreDeJourInput.addEventListener("valueAdded", calculTotalForfaitaire);

  function calculTotalForfaitaire() {
    if (supplementJournalierInput.value === "") {
      const nombreDeJour = parseInt(nombreDeJourInput.value);
      const indemniteForfaitaireJournaliere = parseInt(
        indemniteForfaitaireJournaliereInput.value.replace(/[^\d]/g, "")
      );
      totalindemniteForfaitaireInput.value = formatNumberInt(
        nombreDeJour * indemniteForfaitaireJournaliere
      );
    } else {
      const supplementJournalier = parseInt(
        supplementJournalierInput.value.replace(/[^\d]/g, "")
      );
      const nombreDeJour = parseInt(nombreDeJourInput.value);
      const indemniteForfaitaireJournaliere = parseInt(
        indemniteForfaitaireJournaliereInput.value.replace(/[^\d]/g, "")
      );

      totalindemniteForfaitaireInput.value = formatNumberInt(
        nombreDeJour * (indemniteForfaitaireJournaliere + supplementJournalier)
      );
    }

    const event = new Event("valueAdded");
    totalindemniteForfaitaireInput.dispatchEvent(event);
  }
  //si l'utilisateur saisie une suplement journalier
  supplementJournalierInput.addEventListener(
    "input",
    calculTotalForfaitaireAvecSupplement
  );
  function calculTotalForfaitaireAvecSupplement() {
    supplementJournalierInput.value = formatNumberInt(
      supplementJournalierInput.value
    );
    calculTotalForfaitaire();
  }

  /** CALCUL TOTAL MONTANT AUTRES DEPENSE */
  const autreDepenseInput_1 = document.querySelector(
    "#dom_form2_autresDepense1"
  );
  const autreDepenseInput_2 = document.querySelector(
    "#dom_form2_autresDepense2"
  );
  const autreDepenseInput_3 = document.querySelector(
    "#dom_form2_autresDepense3"
  );
  const totaAutreDepenseInput = document.querySelector(
    "#dom_form2_totalAutresDepenses"
  );

  autreDepenseInput_1.addEventListener("input", () => {
    autreDepenseInput_1.value = formatNumberInt(autreDepenseInput_1.value);
    calculTotal();
  });
  autreDepenseInput_2.addEventListener("input", () => {
    autreDepenseInput_2.value = formatNumberInt(autreDepenseInput_2.value);
    calculTotal();
  });
  autreDepenseInput_3.addEventListener("input", () => {
    autreDepenseInput_3.value = formatNumberInt(autreDepenseInput_3.value);
    calculTotal();
  });

  function calculTotal() {
    const autreDepense_1 =
      parseInt(autreDepenseInput_1.value.replace(/[^\d]/g, "")) || 0;
    const autreDepense_2 =
      parseInt(autreDepenseInput_2.value.replace(/[^\d]/g, "")) || 0;
    const autreDepense_3 =
      parseInt(autreDepenseInput_3.value.replace(/[^\d]/g, "")) || 0;
    let totaAutreDepense = autreDepense_1 + autreDepense_2 + autreDepense_3;

    totaAutreDepenseInput.value = formatNumberInt(totaAutreDepense);
    const event = new Event("valueAdded");
    totaAutreDepenseInput.dispatchEvent(event);
  }

  /** CALCUL  MONTANT TOTAL */
  const montantTotalInput = document.querySelector(
    "#dom_form2_totalGeneralPayer"
  );
  totalIdemniteDeplacementInput.addEventListener("valueAdded", calculTotal);
  totalindemniteForfaitaireInput.addEventListener("valueAdded", calculTotal);
  totaAutreDepenseInput.addEventListener("valueAdded", calculTotal);

  function calculTotal() {
    const totaAutreDepense =
      parseInt(totaAutreDepenseInput.value.replace(/[^\d]/g, "")) || 0;
    const totalIdemniteDeplacement =
      parseInt(totalIdemniteDeplacementInput.value.replace(/[^\d]/g, "")) || 0;
    const totalindemniteForfaitaire =
      parseInt(totalindemniteForfaitaireInput.value.replace(/[^\d]/g, "")) || 0;

    let montantTotal =
      totalindemniteForfaitaire + totaAutreDepense - totalIdemniteDeplacement;

    montantTotalInput.value = formatNumberInt(montantTotal);
  }

  /** CHANGEMENT DE LABEL MODE DE PAIEMENT */
  const modePayementInput = document.querySelector("#dom_form2_modePayement");
  const modeInput = document.querySelector("#dom_form2_mode");
  const labelMode = modeInput.previousElementSibling;
  const matriculeInput_2 = document.querySelector("#dom_form2_matricule");
  modePayementInput.addEventListener("change", infoPersonnel);

  function infoPersonnel($data) {
    const matricule = matriculeInput_2.value;
    let url = `/Hffintranet/personnel-fetch/${matricule}`;
    fetch(url)
      .then((response) => response.json())
      .then((personne) => {
        console.log(personne);
        if (modePayementInput.value === "VIREMENT BANCAIRE") {
          modeInput.readOnly = true;
          modeInput.value = personne.compteBancaire;
        } else {
          modeInput.readOnly = false;
          modeInput.value = "";
        }
        labelMode.innerHTML = modePayementInput.value;
      })
      .catch((error) => console.error("Error:", error));
  }
});
