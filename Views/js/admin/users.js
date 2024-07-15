/**
 * SELECTE 2/ permet de faire une recherche sur le select
 */
$(document).ready(function () {
  $(".selectUser").select2({
    placeholder: "-- Choisir un nom d'utilisateur --",
    allowClear: true,
    theme: "bootstrap",
  });

  $(".superieurs").select2({
    placeholder: "-- Choisir une superieur--",
    allowClear: true,
    theme: "bootstrap",
    width: "100%",
  });
});

/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
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
