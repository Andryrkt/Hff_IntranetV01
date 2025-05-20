export function boutonRadio() {
  const radioButtons = document.querySelectorAll('input[type="radio"]');
  console.log(radioButtons);

  let lastCheckedRadio = null;
  let selectedValues = [];

  function toggleRadio(radio) {
    const valeur = radio.value;
    const prefix = valeur.split("-")[0];

    lastCheckedRadio = radio;
    selectedValues = selectedValues.filter(
      (item) => item.split("-")[0] !== prefix
    );
    selectedValues.push(valeur);

    console.log("Valeurs sélectionnées :", selectedValues);
  }

  // Écouteur sur chaque radio
  radioButtons.forEach((radio) => {
    radio.addEventListener("change", function () {
      toggleRadio(this);
    });

    if (radio.checked) {
      toggleRadio(radio);
    }
  });

  // Appel de toggleRadio si le bouton est déjà sélectionné au chargement

  // Avant soumission du formulaire
  document.getElementById("myForm").addEventListener("submit", function (e) {
    // Met à jour l’input caché avec les valeurs sélectionnées
    const hiddenInput = document.getElementById("refsHiddenInput");
    hiddenInput.value = selectedValues.join(","); // ex: "1-5,4-9"
  });
}
