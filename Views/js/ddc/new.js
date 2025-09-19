document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeauNewConge = document.querySelector("#bandeau-new-conge");
  if (content) {
    content.classList.add("bg-orange-cat");
  }
  if (bandeauNewConge) {
    bandeauNewConge.classList.add("bg-orange-cat");
  }
});
