
function Mat() {
    let Matr = document.getElementById('matricule').value;
    var pj = document.getElementById('PJ');
    var libPJ = document.getElementById('label_PJ')
    if (Matr.length > 4) {
        libPJ.style.display = 'block';
        pj.style.display = 'block';
    } else {
        libPJ.style.display = 'none';
        pj.style.display = 'none';
    }
}

window.addEventListener('load', function() {

    Mat();
});
