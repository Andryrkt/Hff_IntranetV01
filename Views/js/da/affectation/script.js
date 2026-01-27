import { getAllReferences } from "../data/fetchData";

document.addEventListener("DOMContentLoaded", async function () {
  const { data } = await getAllReferences();
  console.log(data);

  document.querySelectorAll(".da-art-refp").forEach((refp) => {
    refp.addEventListener("input", function () {
      refp.value = refp.value.toUpperCase().slice(0, 35);
    });

    refp.addEventListener("blur", async function () {
      const refpValue = refp.value.trim();
      if (refpValue === "") return;

      const articleFound = data.find((ref) => ref.reference === refpValue);
      const found = typeof articleFound !== "undefined";
      const articleStocke = getInputLine(refp, '[id$="_articleStocke"]');
      const desi = getInputLine(refp, '[id$="_artDesi"]');
      const constp = getInputLine(refp, '[id$="_artConstp"]');
      const prix = getInputLine(refp, '[id$="_prixUnitaire"]');
      const numFrn = getInputLine(refp, '[id$="_numeroFournisseur"]');
      const nomFrn = getInputLine(refp, '[id$="_nomFournisseur"]');

      articleStocke.checked = false;
      if (!found) {
        await Swal.fire({
          icon: "error",
          title: "Référence inexistant",
          html: "La référence saisie n'exsite pas pour la liste de constructeurs </br> (<b>'ALI', 'BOI', 'CEN', 'FBU', 'HAB', 'OUT', 'ZDI'</b>)</br> Veuillez en saisir une dans la liste s'il vous plaît.",
        });
        refp.value = "";
        desi.value = "";
        nomFrn.value = "";
        prix.value = "0";
        numFrn.value = "-";
        constp.value = "-";
        desi.classList.remove("non-modifiable");
        nomFrn.classList.remove("non-modifiable");
        refp.focus();
      } else {
        if (articleFound.constp === "ZDI") {
          constp.value = "ZDI";
          prix.value = "0";
          desi.classList.remove("non-modifiable");
          nomFrn.classList.remove("non-modifiable");
        } else {
          articleStocke.checked = true;
          constp.value = articleFound.constp;
          desi.value = articleFound.desi;
          nomFrn.value = articleFound.nom_frn;
          prix.value = articleFound.prix_unitaire;
          numFrn.value = articleFound.num_frn;
          desi.classList.add("non-modifiable");
          nomFrn.classList.add("non-modifiable");
        }
      }
    });
  });

  document.querySelectorAll(".da-art-desi").forEach((desi) => {
    desi.addEventListener("input", function () {
      desi.value = desi.value.toUpperCase().slice(0, 35);
    });
  });

  document.querySelectorAll(".da-nom-frn").forEach((frn) => {
    frn.addEventListener("input", function () {
      frn.value = frn.value.toUpperCase().slice(0, 50);
    });
  });

  function getInputLine(el, selector) {
    return el.parentElement.parentElement.querySelector(selector);
  }
});
