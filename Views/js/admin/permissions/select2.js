$(document).ready(function () {
  $("#permissions_agenceServices").select2({
    placeholder: "-- Choisir des agences - services --",
    allowClear: true,
    theme: "bootstrap",
  });

  $("#permissions_agenceServices").on("select2:open", function () {
    if ($(".select2-select-all").length) return;

    const $dropdown = $(".select2-dropdown");

    const $btn = $(`
      <div class="select2-select-all">
        <button type="button">
          Tout sélectionner (résultats affichés)
        </button>
      </div>
    `);

    $dropdown.prepend($btn);

    $btn.on("click", "button", function () {
      const $select = $("#permissions_agenceServices");
      const searchTerm = $(".select2-search__field").val().toLowerCase();

      const valuesToAdd = [];
      $select.find("option").each(function () {
        const label = $(this).text().toLowerCase();
        if (!searchTerm || label.includes(searchTerm)) {
          valuesToAdd.push($(this).val());
        }
      });

      const currentValues = $select.val() || [];
      const merged = [...new Set([...currentValues, ...valuesToAdd])];
      $select.val(merged).trigger("change");
      // Fermer le dropdown
      $select.select2("close");
    });
  });
});
