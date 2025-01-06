export function validateField(clickedButton, value, conditionFn, errorElement) {
  if (!conditionFn(value) && clickedButton) {
    errorElement.style.display = 'block';
    return false;
  }
  errorElement.style.display = 'none';
  return true;
}

export function toggleRequiredFields(
  fieldsToMakeEnabled,
  fieldsToMakeRequired,
  fieldsToRemoveRequired
) {
  fieldsToMakeEnabled.forEach((field) => (field.disabled = false));
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
      if (element.type !== 'submit') {
        element.disabled = true;
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
