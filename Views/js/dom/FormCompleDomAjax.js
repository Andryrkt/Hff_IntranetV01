$(document).ready(function () {
  // Fonction pour gérer le changement du champ ServINt
  function handleServINtChange() {
    var valeurCode = $("#ServINt").val();
    var typeMission = $("#typeMission").val();
    var codeServ = valeurCode.substring(0, 2);
    if (typeMission === "MUTATION" && codeServ === "50") {
      $.ajax({
        type: "POST",
        url: "/Hffintranet/selectCatgeRental",
        data: {
          CodeRental: codeServ,
        },
        success: function (response) {
          $("#MUTARENTAL").html(response).show();
          handleSiteRental();
        },
        error: function (error) {
          console.error(error);
        },
      });
    } else {
      $("#MUTARENTAL").hide();
    }
  }

  function handleSiteRental() {
    var MutatRental = $("#MUTARENTAL option:selected").text();
    MutaRental = MutatRental.replace(/\+/g, " "); //categorie select
    var catgePErs = $("#catego").val(); //no rental
    CatgePers = catgePErs.replace(/\+/g, " ");
    var valeurCode = $("#ServINt").val();
    var typeMission = $("#typeMission").val();
    var codeServ = valeurCode.substring(0, 2);
    if (MutaRental.trim() !== "") {
      if (typeMission === "MUTATION" && codeServ === "50") {
        $.ajax({
          type: "POST",
          url: "/Hffintranet/selectIdem",
          data: {
            CategPers: MutaRental,
            TypeMiss: typeMission,
          },
          success: function (response1) {
            setTimeout(() => {
              $("#SITE").html(response1).show();
              handlePrixRental();
            }, 2000);
          },
          error: function (error) {
            console.error(error);
          },
        });
      }
    }

    if (typeMission === "MUTATION" && codeServ !== "50") {
      $.ajax({
        type: "POST",
        url: "/Hffintranet/selectIdem",
        data: {
          CategPers: CatgePers,
          TypeMiss: typeMission,
        },
        success: function (response1) {
          $("#SITE").html(response1).show();
          handlePrixRental();
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
    if (typeMission === "MISSION") {
      $.ajax({
        type: "POST",
        url: "/Hffintranet/selectIdem",
        data: {
          CategPers: CatgePers,
          TypeMiss: typeMission,
        },
        success: function (response1) {
          $("#SITE").html(response1).show();
          handlePrixRental();
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
  }

  function handlePrixRental() {
    var SiteRental = $("#SITE option:selected").text();
    SiteRental01 = SiteRental.replace(/\+/g, " ");
    var MutatRental = $("#MUTARENTAL option:selected").text();
    MutaRental = MutatRental.replace(/\+/g, " ");
    var valeurCode = $("#ServINt").val();
    var typeMission = $("#typeMission").val();
    var codeServ = valeurCode.substring(0, 2);
    if (SiteRental01.trim() !== "") {
      if (typeMission === "MUTATION" && codeServ === "50") {
        $.ajax({
          type: "POST",
          url: "/Hffintranet/selectPrixRental",
          data: {
            typeMiss: typeMission,
            categ: MutaRental,
            siteselect: SiteRental01,
            codeser: codeServ,
          },
          success: function (PrixRental) {
            $("#idemForfait").val(PrixRental).show();
          },
          error: function (error) {
            console.error(error);
          },
        });
      }
    }
    if (typeMission === "MUTATION" && codeServ !== "50") {
      var catgePErs = $("#catego").val();
      CatgePers = catgePErs.replace(/\+/g, " ");
      $.ajax({
        type: "POST",
        url: "/Hffintranet/selectPrixRental",
        data: {
          typeMiss: typeMission,
          categ: CatgePers,
          siteselect: SiteRental01,
          codeser: codeServ,
        },
        success: function (PrixRental) {
          $("#idemForfait").val(PrixRental).show();
          $("#idemForfait01").val(PrixRental).show();
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
    if (typeMission === "MISSION") {
      var catgePErs = $("#catego").val();
      CatgePers = catgePErs.replace(/\+/g, " ");
      $.ajax({
        type: "POST",
        url: "/Hffintranet/selectPrixRental",
        data: {
          typeMiss: typeMission,
          categ: CatgePers,
          siteselect: SiteRental01,
          codeser: codeServ,
        },
        success: function (PrixRental) {
          $("#idemForfait").val(PrixRental).show();
          //$('#idemForfait01').val(PrixRental).show();
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
  }

  function verifiationTYpeMission() {
    var TypeMission = $("#typeMission").val();
    var idemite_jour = $("#idemForfait");
    var TelMobile = $("#modeMob");
    if (TypeMission === "FRAIS EXCEPTIONNEL") {
      idemite_jour.prop("readonly", false);
    } else {
      idemite_jour.prop("readonly", true);
    }
    if (TypeMission === "FRAIS EXCEPTIONNEL") {
      TelMobile.prop("required", false);
    } else {
      TelMobile.prop("required", true);
    }
  }

  function MobileMoney() {
    var TelMobileval = $("#modeMob").val();
    var TelMobile = $("#modeMob");
    var typeMode = $("#modepaie option:selected").val();
    var check = $("#radiochek").val();

    if (typeMode !== "MOBILE MONEY") {
      TelMobile.prop("required", false);
    } else if (
      typeMode === "MOBILE MONEY" &&
      (TelMobileval === undefined || TelMobileval.trim() === "") &&
      check === "Interne"
    ) {
      TelMobile.prop("required", true);
    } else {
      TelMobile.prop("required", false);
    }
  }

  function FicheAtelier() {
    var check = $("#radiochek").val();
    var libservINT = $("#LibServINT").val();
    var libservEXT = $("#LibServ").val();
    var fiche = $("#fiche");

    var servlib;

    if (check === "Interne") {
      servlib = libservINT.substring(0, 3);
    } else {
      servlib = libservEXT.substring(0, 3);
    }

    var valService = ["MAS", "ATE", "CSP"];
    var motTrouver = valService.some(function (mot) {
      return servlib.includes(mot);
    });

    fiche.prop("required", motTrouver);
    console.log(servlib);
  }

  $("#ServINt").on("input", function () {
    handleServINtChange();
  });

  $("#MUTARENTAL").change(function () {
    handleSiteRental();
  });
  $("#SITE").change(function () {
    handlePrixRental();
  });

  $("#modepaie").change(function () {
    MobileMoney();
  });

  handleServINtChange();
  handleSiteRental();
  handlePrixRental();
  verifiationTYpeMission();
  MobileMoney();
  FicheAtelier();
});

/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
const agenceDebiteurInterneInput = document.querySelector(
  "#agenceDebiteurInterne"
);
const serviceDebiteurInterneInput = document.querySelector(
  "#serviceDebiteurInterne"
);
console.log(agenceDebiteurInterneInput);

const agenceDebiteurExterneInput = document.querySelector(
  "#agenceDebiteurExterne"
);
const serviceDebiteurExterneInput = document.querySelector(
  "#serviceDebiteurExterne"
);
const interneExternInput = document.querySelector("#radiochek");
console.log(interneExternInput.value);
if (interneExternInput.value === "Externe") {
  agenceDebiteurExterneInput.addEventListener("change", selectAgenceExterne);
} else {
  agenceDebiteurInterneInput.addEventListener("change", selectAgenceInterne);
}

function selectAgenceInterne() {
  const agenceDebiteur = agenceDebiteurInterneInput.value;
  let url = `/Hffintranet/serviceDebiteur-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      console.log(services[1].serviceDebiteur);

      // Supprimer toutes les options existantes
      while (serviceDebiteurInterneInput.options.length > 0) {
        serviceDebiteurInterneInput.remove(0);
      }

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < services.length; i++) {
        var option = document.createElement("option");
        option.value = services[i].serviceDebiteur;
        option.text = services[i].serviceDebiteur;
        serviceDebiteurInterneInput.add(option);
      }
    })
    .catch((error) => console.error("Error:", error));
}

/** EXTERNE */

function selectAgenceExterne() {
  const agenceDebiteur = agenceDebiteurExterneInput.value;
  let url = `/Hffintranet/serviceDebiteur-fetch/${agenceDebiteur}`;
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      console.log(services[1].serviceDebiteur);

      // Supprimer toutes les options existantes
      while (serviceDebiteurExterneInput.options.length > 0) {
        serviceDebiteurExterneInput.remove(0);
      }

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < services.length; i++) {
        var option = document.createElement("option");
        option.value = services[i].serviceDebiteur;
        option.text = services[i].serviceDebiteur;
        serviceDebiteurExterneInput.add(option);
      }
    })
    .catch((error) => console.error("Error:", error));
}
