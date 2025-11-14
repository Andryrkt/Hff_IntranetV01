import { displayOverlay } from "../../utils/ui/overlay";
import { swalOptions } from "../listeCdeFrn/ui/swalUtils";

document.addEventListener("DOMContentLoaded", function () {
  const myForm = document.getElementById("myForm");
  const observation = document.getElementById(
    "da_observation_validation_observation"
  );
  const message = {
    pendingAction: {
      refuser: `Refus de la demande en cours, merci de patienter ...`,
      valider: `Validation de la demande en cours, merci de patienter ...`,
    },
  };

  myForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    const action = e.submitter.name;
    if (action === "refuser" && !observation.value.trim()) {
      await Swal.fire({
        ...swalOptions.observationRequise,
        didClose: () => observation.focus(), // action qui s'éxecute juste après la fermeture de la modale
      });
    } else {
      const confirmation = await Swal.fire(
        swalOptions.getConfirmConfig(action)
      );
      if (confirmation.isConfirmed) {
        displayOverlay(true, message.pendingAction[action]);
        // ajouter un champ caché avec l’action choisie
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = action;
        hidden.value = "1";
        myForm.appendChild(hidden);

        myForm.submit(); // n’émule pas le clic sur le bouton d’envoi → donc le name et value du bouton cliqué ne sont pas envoyés.
      } else Swal.fire(swalOptions.getAnnulationOperation(action));
    }
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
