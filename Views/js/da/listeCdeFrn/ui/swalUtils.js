export async function showSwal(config) {
  return Swal.fire({
    ...config,
    customClass: { htmlContainer: "swal-text-left" },
  });
}

export function getConfirmConfig(actionType, count) {
  const configs = {
    delete: {
      title: "Confirmer la suppression",
      text: `Voulez-vous vraiment supprimer ${count} article(s) ?`,
      icon: "warning",
      confirmButtonText: "Oui, supprimer",
      confirmButtonColor: "#d33",
      cancelButtonText: "Annuler",
    },
    create: {
      title: "Confirmer la création",
      text: `Voulez-vous vraiment créer ${count} article(s) ?`,
      icon: "question",
      confirmButtonText: "Oui, créer",
      confirmButtonColor: "#198754",
      cancelButtonText: "Annuler",
    },
  };
  return configs[actionType];
}
