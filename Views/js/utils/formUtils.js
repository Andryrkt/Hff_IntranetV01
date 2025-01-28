export function validateField(clickedButton, value, conditionFn, errorElement) {
  if (!conditionFn(value) && clickedButton) {
    errorElement.style.display = 'block';
    return false;
  }
  errorElement.style.display = 'none';
  return true;
}

export function validateFieldDate(
  clickedButton,
  dateDebutvalue,
  dateFinvalue,
  errorElement
) {
  if (clickedButton) {
    let textError = '';
    let dateDebut = new Date(dateDebutvalue);
    let dateFin = new Date(dateFinvalue);
    if (dateDebut < new Date().setHours(0, 0, 0, 0)) {
      textError =
        "Le jour de la date de début pour le planning doit être supérieur ou égale à aujourd'hui.";
    } else if (dateDebut.getDay() === 0 || dateDebut.getDay() === 6) {
      textError =
        'Le jour de la date de début pour le planning ne doit pas être un Samedi ou Dimanche.';
    } else if (dateFin.getDay() === 0 || dateFin.getDay() === 6) {
      textError =
        'Le jour de la date de fin pour le planning ne doit pas être un Samedi ou Dimanche.';
    } else if (dateDebut > dateFin) {
      textError =
        'La date de fin pour le planning doit être supérieur à celui de la date de début.';
    }

    if (textError !== '') {
      errorElement.innerHTML = textError;
      errorElement.style.display = 'block';
      return false;
    }
  }
  errorElement.style.display = 'none';
  return true;
}

export function disableErrorElements(...errorElements) {
  errorElements.forEach((errorElement) => {
    if (errorElement instanceof HTMLElement) {
      errorElement.style.display = 'none';
    }
  });
}

export function toggleRequiredFields(
  fieldsToMakeEnabled,
  fieldsToMakeRequired,
  fieldsToRemoveRequired
) {
  fieldsToMakeEnabled.forEach((field) =>
    field.classList.remove('non-modifiable')
  );
  fieldsToMakeRequired.forEach((field) => {
    addAsterisk(field);
    field.setAttribute('required', 'required');
  });
  fieldsToRemoveRequired.forEach((field) => {
    removeAsterisk(field);
    field.removeAttribute('required');
  });
}

export function validateFormBeforeSubmit(event, validations) {
  let isValid = true;
  validations.forEach((validation) => {
    if (!validation()) isValid = false;
  });
  if (!isValid) event.preventDefault();
}

export function disableForm(formId) {
  const form = document.getElementById(formId);
  if (form) {
    Array.from(form.elements).forEach((element) => {
      if (element.type !== 'submit' && element.type !== 'hidden') {
        element.classList.add('non-modifiable');
      }
    });
  } else {
    console.error(`Element with ID "${formId}" not found.`);
    return;
  }
}

function addAsterisk(field) {
  if (field.id == 'detail_tik_commentaires') {
    const divLabel = document.querySelector('#commentaire');
    const label = divLabel.querySelector('h3');
    if (!divLabel.querySelector('.required')) {
      const asterisk = document.createElement('span');
      asterisk.classList.add('required');
      asterisk.textContent = ' (*)';
      label.appendChild(asterisk);
    }
  } else {
    const label = document.querySelector(`label[for='${field.id}']`);
    if (label) {
      // Vérifier si l'astérisque est déjà présent
      if (!label.querySelector('.required')) {
        const asterisk = document.createElement('span');
        asterisk.classList.add('required');
        asterisk.textContent = ' (*)';
        label.appendChild(asterisk);
      }
    }
  }
}

function removeAsterisk(field) {
  if (field.id == 'detail_tik_commentaires') {
    const divLabel = document.querySelector('#commentaire');
    const asterisk = divLabel.querySelector('.required');
    if (asterisk) {
      asterisk.remove();
    }
  } else {
    const label = document.querySelector(`label[for='${field.id}']`);
    if (label) {
      const asterisk = label.querySelector('.required');
      if (asterisk) {
        asterisk.remove();
      }
    }
  }
}
