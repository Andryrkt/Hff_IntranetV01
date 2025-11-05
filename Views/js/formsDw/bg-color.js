document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeau = document.querySelector("#bandeau");
  if (content) content.classList.add(bandeau.dataset.bgColor);
  if (bandeau) bandeau.classList.add(bandeau.dataset.bgColor);
});
