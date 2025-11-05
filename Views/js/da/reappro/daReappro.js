document.addEventListener("DOMContentLoaded", function () {
  const allQteDemDiv = document.querySelectorAll(".qte-dem-da-reappro");
  if (!allQteDemDiv) return;
  allQteDemDiv.forEach((qteDemDiv) => {
    let qteDem = parseInt(qteDemDiv.dataset.qteDem, 10) || 0;
    let qteValide = parseInt(qteDemDiv.dataset.qteValide, 10) || 0;
    if (qteDem > qteValide) qteDemDiv.classList.add("qte-depasse");
  });
});
