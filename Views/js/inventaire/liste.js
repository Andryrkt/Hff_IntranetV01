document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('inventaire_search_agence_all');

    checkAll.addEventListener('click', function () {
        const allInputCheckbox = document.querySelectorAll('.form-check-input');

        allInputCheckbox.forEach((inputCheckbox)=> {
            inputCheckbox.checked = checkAll.checked;
        });
    });
});