document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeau = document.querySelector("#bandeau-orange-cat");
  if (content) content.classList.add("bg-orange-cat");
  if (bandeau) bandeau.classList.add("bg-orange-cat");
});
