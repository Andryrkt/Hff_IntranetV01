import { FetchManager } from "../../api/FetchManager.js";
document.addEventListener("DOMContentLoaded", function () {
  const btnsStopRelance = document.querySelectorAll(".js-btn-stop-relance");
  const fetchManager = new FetchManager();

  btnsStopRelance.forEach((btn) => {
    btn.addEventListener("click", stopOuRelance);
  });

  function stopOuRelance(event) {
    event.preventDefault();
    const btn = event.currentTarget;
    const numeroDevis = btn.dataset.numeroDevis;
    const isCurrentlyStopped = btn.textContent.trim() === "OUI";

    const action = isCurrentlyStopped ? "réactiver" : "arrêter";

    Swal.fire({
      title: "Confirmation",
      text:
        "Voulez-vous vraiment " +
        action +
        " la relance pour le devis " +
        numeroDevis +
        " ?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Oui, valider",
      cancelButtonText: "Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        const overlay = document.getElementById("loading-overlays");
        if (overlay) overlay.classList.add("active");

        const endpoint = "api/stop-relance/" + numeroDevis;

        fetchManager
          .post(endpoint, {})
          .then((data) => {
            if (overlay) overlay.classList.remove("active");
            if (data.success) {
              const newIsStopped = !isCurrentlyStopped;

              // Mise à jour visuelle du bouton sans rechargement
              if (newIsStopped) {
                btn.textContent = "OUI";
                btn.classList.remove("btn-warning");
                btn.classList.add("btn-success");
              } else {
                btn.textContent = "NON";
                btn.classList.remove("btn-success");
                btn.classList.add("btn-warning");
              }

              Swal.fire({
                title: "Succès !",
                text:
                  "Relance " +
                  (newIsStopped ? "arrêtée" : "réactivée") +
                  " avec succès.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
              });
            } else {
              Swal.fire({
                title: "Erreur",
                text:
                  "Erreur lors de l'opération : " +
                  (data.message || "Erreur inconnue"),
                icon: "error",
              });
            }
          })
          .catch((error) => {
            if (overlay) overlay.classList.remove("active");
            console.error("Error:", error);
            Swal.fire({
              title: "Erreur",
              text: "Une erreur est survenue lors de la communication avec le serveur.",
              icon: "error",
            });
          });
      }
    });
  }
});
