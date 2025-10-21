document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeau = document.querySelector("#bandeau-bleu-hff");
  if (content) content.classList.add("bg-bleu-hff");
  if (bandeau) bandeau.classList.add("bg-bleu-hff");
});
