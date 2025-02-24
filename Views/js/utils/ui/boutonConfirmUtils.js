export function setupConfirmationButtons() {
  document.querySelectorAll("[data-confirmation]").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      const overlay = document.getElementById("loading-overlay");
      const formSelector = button.getAttribute("data-form");
      const messageConfirmation =
        button.getAttribute("data-confirmation-message") || "Êtes-vous sûr ?";
      const messageAvertissement =
        button.getAttribute("data-warning-message") ||
        "Veuillez de ne pas fermer l’onglet durant le traitement.";
      const messageText =
        button.getAttribute("data-confirmation-text") ||
        "Vous êtes en train de faire une soumission à validation dans DocuWare";

      Swal.fire({
        title: messageConfirmation,
        text: messageText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#fbbb01",
        cancelButtonColor: "#d33",
        confirmButtonText: "OUI",
      })
        .then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: "Fait Attention!",
              text: messageAvertissement,
              icon: "warning",
            }).then(() => {
              overlay.classList.remove("hidden");

              let form;
              if (formSelector) {
                form = document.querySelector(formSelector);
              } else {
                form = button.closest("form");
              }

              if (form) {
                form.submit();
              } else {
                console.error("Formulaire non trouvé: ", formSelector);
              }
            });
          }
        })
        .finally(() => {
          overlay.classList.add("hidden");
        });
    });
  });
}
