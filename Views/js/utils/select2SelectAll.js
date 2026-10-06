/**
 * Ajoute un bouton "Tout sélectionner" au dropdown Select2.
 * @param {string} selector - Sélecteur jQuery du <select> cible
 * @param {Object} [options] - Options de configuration
 * @param {string} [options.placeholder] - Placeholder du Select2
 * @param {boolean} [options.allowClear] - Autoriser la suppression de la sélection
 * @param {string} [options.theme] - Thème Select2
 * @param {string} [options.btnLabel] - Texte du bouton "Tout sélectionner"
 */
export function initSelect2WithSelectAll(selector, options = {}) {
  const {
    placeholder = "",
    allowClear = true,
    theme = "bootstrap",
    btnLabel = "Tout sélectionner",
  } = options;

  const $select = $(selector);

  if (!$select.length) {
    console.warn(
      `initSelect2WithSelectAll : aucun élément trouvé pour "${selector}"`
    );
    return;
  }

  $select.select2({ placeholder, allowClear, theme });

  $select.on("select2:open", function () {
    if ($(".select2-select-all").length) return;

    const $dropdown = $(".select2-dropdown");
    const $btn = $(`
      <div class="select2-select-all">
        <button type="button">${btnLabel}</button>
      </div>
    `);

    $dropdown.prepend($btn);

    $btn.on("click", "button", function () {
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
      $select.select2("close");
    });
  });
}
