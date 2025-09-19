document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeauNewConge = document.querySelector("#bandeau-new-contrat");
  if (content) {
    content.classList.add("bg-bleu-hff");
  }
  if (bandeauNewConge) {
    bandeauNewConge.classList.add("bg-bleu-hff");
  }
});
