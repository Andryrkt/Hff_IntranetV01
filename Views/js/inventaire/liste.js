document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('inventaire_search_agence_all');
    checkAllCheckbox(true);
    
    checkAll.addEventListener('click', () => checkAllCheckbox());
 
    function checkAllCheckbox(checked = false) {
        const allInputCheckbox = document.querySelectorAll('.form-check-input');
        allInputCheckbox.forEach((inputCheckbox)=> {
            checkAll.checked = checked ? true : checkAll.checked;
            inputCheckbox.checked = checkAll.checked;
        });
    }
});