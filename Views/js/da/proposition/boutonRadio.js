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

    const refsValide = selectedValues.reduce((acc, item) => {
      const [key, value] = item.split("-");
      acc[key] = value; // Utilise le premier élément comme clé et le second comme valeur
      return acc; // Accumulate les paires clé-valeur
    }, {}); // Crée un objet avec les paires clé-valeur, {} est l'initialisation

    localStorage.setItem("selectedValues", JSON.stringify(refsValide));

    const tableBody = radio.closest("tbody");
    const radiosInTableBody = tableBody.querySelectorAll('input[type="radio"]');
    radiosInTableBody.forEach((r) => {
      let row = r.closest("tr");
      if (r === radio) {
        row.classList.add("table-active");
      } else {
        row.classList.remove("table-active");
      }
    });
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
