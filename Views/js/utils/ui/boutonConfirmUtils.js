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

      // Validation générale des champs obligatoires
      const generalValidation = validateFormFields(form);
      if (!generalValidation.isValid) {
        Swal.fire({
          title: "Champs obligatoires manquants",
          html: generalValidation.errors.join("<br>"),
          icon: "error",
        });
        return;
      }

      // Validation spécifique au formulaire (importée depuis l'autre fichier)
      try {
        const { validateSpecificForm } =
          await import("./form-specific-validation.js");
        const specificValidation = await validateSpecificForm(
          form,
          formSelector,
        );

        if (!specificValidation.isValid) {
          Swal.fire({
            title: specificValidation.title || "Erreur de validation",
            html: specificValidation.message,
            icon: "error",
          });
          return;
        }
      } catch (error) {
        console.error(
          "Erreur lors du chargement des validations spécifiques:",
          error,
        );
        // Continuer sans validation spécifique si le fichier n'est pas trouvé
      }

      const messages = {
        confirmation:
          button.getAttribute("data-confirmation-message") || "Êtes-vous sûr ?",
        warning:
          button.getAttribute("data-warning-message") ||
          "Veuillez ne pas fermer l'onglet durant le traitement.",
        text:
          button.getAttribute("data-confirmation-text") ||
          "Vous êtes en train de faire une soumission à validation dans DocuWare",
      };

      const isConfirmed = await showConfirmationDialog(messages);
      if (!isConfirmed) return;

      await showWarningDialog(messages.warning);

      setTimeout(() => {
        overlay.style.display = "flex";
        button.disabled = true;
      }, 100);

      try {
        form.submit();
      } catch (error) {
        console.error("Erreur lors de la soumission du formulaire:", error);
        overlay.style.display = "none";
        button.disabled = false;
      }
    });
  });
}

// Validation générale des champs obligatoires
function validateFormFields(form) {
  let isValid = true;
  const errors = [];
  const requiredFields = form.querySelectorAll("[required]");
  const validatedRadioGroups = new Set(); // Pour éviter de valider le même groupe radio plusieurs fois

  requiredFields.forEach((field) => {
    const errorElement = document.querySelector(`#error-${field.id}`);
    const fieldName = field.dataset.fieldName || field.name || field.id;

    const handleInvalidField = (message) => {
      isValid = false;
      if (!errors.some(e => e.includes(fieldName))) {
        errors.push(message);
      }
      if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add("text-danger");
      }
    };

    const handleValidField = () => {
      if (errorElement) {
        errorElement.textContent = "";
      }
    };

    const errorMessage = `Le champ "<span class="text-danger text-decoration-underline">${fieldName}</span>" est obligatoire`;

    if (field.type === 'radio') {
      const groupName = field.name;
      if (validatedRadioGroups.has(groupName)) return;
      validatedRadioGroups.add(groupName);

      const group = form.querySelectorAll(`input[name="${groupName}"]`);
      if (!Array.from(group).some(radio => radio.checked)) {
        handleInvalidField(errorMessage);
        group.forEach(radio => radio.closest('label')?.classList.add('text-danger'));
      } else {
        handleValidField();
        group.forEach(radio => radio.closest('label')?.classList.remove('text-danger'));
      }
    } else if (field.value !== undefined) {
      if (!field.value.trim()) {
        handleInvalidField(errorMessage);
        field.classList.add("border", "border-danger");
      } else {
        handleValidField();
        field.classList.remove("border", "border-danger");
      }
    } else {
      // Cas où 'field' est un conteneur (ex: div pour ChoiceType étendu)
      const radios = field.querySelectorAll('input[type="radio"]');
      if (radios.length > 0) {
        const groupName = radios[0].name;
        if (validatedRadioGroups.has(groupName)) return;
        validatedRadioGroups.add(groupName);

        if (!Array.from(radios).some(radio => radio.checked)) {
          handleInvalidField(errorMessage);
          field.classList.add("border", "border-danger");
        } else {
          handleValidField();
          field.classList.remove("border", "border-danger");
        }
      }
    }
  });

  return { isValid, errors };
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
