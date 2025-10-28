export const swalOptions = {
  confirmSameDa: {
    title: "Êtes-vous sûr(e) ?",
    html: `Vous ne pouvez sélectionner que des lignes appartenant à la même DA.<br>
    Si vous voulez quand même sélectionner ces lignes, cliquez sur <b class="text-success">"Continuer"</b> (les lignes précédemment cochées seront décochées).
    Sinon, cliquez sur <b class="text-secondary">"Annuler"</b>.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Oui, Continuer",
    cancelButtonText: "Annuler",
    customClass: { htmlContainer: "swal-text-left" },
  },
  noArticleSelected: {
    icon: "error",
    title: "Aucun article sélectionné",
    html: `Vous n'avez sélectionné aucun article. Veuillez choisir au moins un article avant de cliquer sur les choix d'action.`,
    confirmButtonText: "OK",
    customClass: { htmlContainer: "swal-text-left" },
  },
  annulationOperation: {
    icon: "info",
    title: "Annulation",
    text: "Opération abandonnée.",
    timer: 2000,
    showConfirmButton: false,
  },
  observationRequise: {
    icon: "warning",
    title: "Observation requise",
    text: "Veuillez saisir une observation avant de refuser la demande.",
  },
  errorGeneric: (error) => ({
    icon: "error",
    title: "Une erreur est survenue",
    html: error?.message || "Une erreur inattendue s'est produite.",
    confirmButtonText: "OK",
  }),
  genericResponse: (result) => ({
    icon: result.status,
    title: result.title,
    html: result.message,
  }),
  getConfirmConfig: (actionType, count = 0) => {
    let configs = {
      delete: {
        title: "Confirmer la suppression",
        text: `Voulez-vous vraiment supprimer ${count} article${
          count > 1 ? "s" : ""
        } ?`,
        icon: "warning",
        confirmButtonText: "Oui, supprimer",
        confirmButtonColor: "#d33",
        cancelButtonText: "Annuler",
        showCancelButton: true,
      },
      create: {
        title: "Confirmer la création",
        text: `Voulez-vous vraiment créer ${count} article${
          count > 1 ? "s" : ""
        } ?`,
        icon: "question",
        confirmButtonText: "Oui, créer",
        confirmButtonColor: "#198754",
        cancelButtonText: "Annuler",
        showCancelButton: true,
      },
      refuser: {
        title: "Confirmer le refus de la demande",
        html: `Souhaitez-vous réellement <strong class="text-danger">refuser</strong> cette demande de réapprovisionnement mensuel ?<br><small class="text-danger"><strong><u>NB</u> :</strong> Cette action est définitive et la demande sera classée comme refusée.</small>`,
        icon: "warning",
        confirmButtonText: "Oui, Refuser",
        confirmButtonColor: "#d33",
        cancelButtonText: "Annuler",
        showCancelButton: true,
        customClass: { htmlContainer: "swal-text-left" },
      },
      valider: {
        title: "Confirmer la validation de la demande",
        html: `Êtes-vous sûr de vouloir <strong class="text-success">valider</strong> cette demande de réapprovisionnement mensuel ?<br><small class="text-success"><strong><u>NB</u> :</strong> Une fois validée, la demande sera soumise à validation dans Docuware.</small>`,
        icon: "question",
        reverseButtons: true,
        confirmButtonText: "Oui, Valider",
        confirmButtonColor: "#198754",
        cancelButtonText: "Annuler",
        showCancelButton: true,
        customClass: { htmlContainer: "swal-text-left" },
      },
    };
    return configs[actionType];
  },
  getAnnulationOperation: (actionType) => {
    let configs = {
      valider: {
        icon: "info",
        title: "Annulation",
        text: "La validation de la demande a été annulée.",
        timer: 2000,
        showConfirmButton: false,
      },
      refuser: {
        icon: "info",
        title: "Annulation",
        text: "Le refus de la demande a été annulé.",
        timer: 2000,
        showConfirmButton: false,
      },
    };
    return configs[actionType];
  },
};
