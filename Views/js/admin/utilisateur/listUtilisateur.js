/** RECHERCHE */
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(".user-search-input");

  searchInputs.forEach((input) => {
    let label = input.dataset.label;
    input.addEventListener("keyup", () => {
      let filter = input.value.toLowerCase();
      let rows = document.querySelectorAll("#tableBody tr:not(.d-none)");

      rows.forEach((row) => {
        let text = row
          .querySelector(`td[data-label="${label}"]`)
          .textContent.toLowerCase();
        row.classList.toggle("d-none", !text.includes(filter));
      });
    });
  });
});
