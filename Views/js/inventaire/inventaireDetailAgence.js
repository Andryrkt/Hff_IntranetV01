document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('inventaire_search_agence_all');
    const allInputCheckbox = document.querySelectorAll('input[name="detail_inventaire_search[agence][]"]');

    let afficherTous = true;
    for (const inputCheckbox of allInputCheckbox) {
        if (inputCheckbox.checked) {
            afficherTous = false;
            break;
        }  
    }

    if (afficherTous) {
        checkAllCheckbox(true);
    }
    
    checkAll.addEventListener('click', () => checkAllCheckbox());
 
    function checkAllCheckbox(checked = false) {
        allInputCheckbox.forEach((inputCheckbox)=> {
            checkAll.checked = checked ? true : checkAll.checked;
            inputCheckbox.checked = checkAll.checked;
        });
    }
});