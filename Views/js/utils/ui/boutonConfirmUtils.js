export function setupConfirmationButtons() {
  document.querySelectorAll("[data-confirmation]").forEach((button) => {
    button.addEventListener("click", async (e) => {
      e.preventDefault();

      const overlay = document.getElementById("loading-overlays");
      const formSelector = button.getAttribute("data-form");
      const form = document.querySelector(formSelector);

      if (!form) {
        console.error("Formulaire non trouvé:", formSelector);
        return;
      }

      // Vérification des champs obligatoires
      const isValid = validateFormFields(form);
      if (!isValid) {
        Swal.fire({
          title: "Champs obligatoires manquants",
          text: "Veuillez remplir tous les champs obligatoires avant de valider.",
          icon: "error",
        });
        return;
      }

      const messages = {
        confirmation:
          button.getAttribute("data-confirmation-message") || "Êtes-vous sûr ?",
        warning:
          button.getAttribute("data-warning-message") ||
          "Veuillez ne pas fermer l’onglet durant le traitement.",
        text:
          button.getAttribute("data-confirmation-text") ||
          "Vous êtes en train de faire une soumission à validation dans DocuWare",
      };
      const isConfirmed = await showConfirmationDialog(messages);
      if (!isConfirmed) return;

      await showWarningDialog(messages.warning);
      setTimeout(() => {
        overlay.style.display = "flex";
        button.disabled = true; // Désactiver le bouton pour éviter les doubles soumissions
      }, 100);

      try {
        form.submit();
      } catch (error) {
        console.error("Erreur lors de la soumission du formulaire:", error);
      } finally {
        overlay.style.display = "none";
        button.disabled = false; // Réactiver le bouton après la soumission
      }
    });
  });
}

// Vérification des champs obligatoires
function validateFormFields(form) {
  let isValid = true;
  const requiredFields = form.querySelectorAll("[required]");

  requiredFields.forEach((field) => {
    const errorElement = document.querySelector(`#error-${field.id}`);
    if (!field.value.trim()) {
      field.classList.add("border", "border-danger");
      if (errorElement) {
        errorElement.textContent = "Ce champ est obligatoire";
        errorElement.classList.add("text-danger");
      }
      isValid = false;
    } else {
      field.classList.remove("border", "border-danger");
      if (errorElement) {
        errorElement.textContent = "";
      }
    }
  });

  return isValid;
}

// Affichage de la boîte de confirmation
async function showConfirmationDialog(messages) {
  const result = await Swal.fire({
    title: messages.confirmation,
    text: messages.text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#fbbb01",
    cancelButtonColor: "#d33",
    confirmButtonText: "OUI",
  });

  return result.isConfirmed;
}

// Affichage de l'avertissement après confirmation
async function showWarningDialog(warningMessage) {
  await Swal.fire({
    title: "Fait Attention!",
    text: warningMessage,
    icon: "warning",
  });
}
