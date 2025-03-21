document.addEventListener('DOMContentLoaded', function () {
  const checkboxes = document.querySelectorAll('.checkbox');
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
      checkboxes.forEach((cb) => {
        if (cb !== this) cb.checked = false;
      });
    });
  });
});
