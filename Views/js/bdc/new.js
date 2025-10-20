document.addEventListener("DOMContentLoaded", function () {
  const content = document.querySelector(".content");
  const bandeauNewConge = document.querySelector("#bandeau-new-bon-de-caisse");
  if (content) {
    content.classList.add("bg-noir");
  }
  if (bandeauNewConge) {
    bandeauNewConge.classList.add("bg-noir");
  }
});
