const requiredFieldsId = [
  "mutation_form_matriculeNomPrenom",
  "mutation_form_categorie",
  "mutation_form_agenceEmetteur",
  "mutation_form_serviceEmetteur",
  "mutation_form_agenceDebiteur",
  "mutation_form_serviceDebiteur",
  "mutation_form_dateDebut",
  // 'mutation_form_client',
  "mutation_form_lieuMutation",
  "mutation_form_motifMutation",
  "mutation_form_avanceSurIndemnite",
];

const requiredForIndemnity = [
  "mutation_form_site",
  "mutation_form_dateFin",
  "mutation_form_modePaiementLabel",
  "mutation_form_modePaiementValue",
];

export function handleAllField(fraisInstallation) {
  requiredFieldsId.forEach((requiredFieldId) => {
    const field = document.querySelector(`#${requiredFieldId}`);
    addRequiredToField(field);
  });
  requiredForIndemnity.forEach((fieldId) => {
    const field = document.querySelector(`#${fieldId}`);
    fraisInstallation === "OUI"
      ? addRequiredToField(field)
      : removeRequiredToField(field);
  });
}

export function addRequiredToField(field) {
  let label = document.querySelector(`label[for=${field.id}]`);
  if (!label.querySelector(".field-required")) {
    let asterisk = document.createElement("span");
    asterisk.classList.add("field-required");
    asterisk.textContent = " (*)";
    label.appendChild(asterisk);
  }
  field.classList.add("border-required");
}

export function removeRequiredToField(field) {
  let label = document.querySelector(`label[for=${field.id}]`);
  let asterisk = label.querySelector(".field-required");
  if (asterisk) {
    label.removeChild(asterisk);
  }
  field.classList.remove("border-required");
}
