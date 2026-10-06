import { initializeFileHandlers } from "../utils/file_upload_Utils.js";

const fileInput1 = document.querySelector("#ddp_dossier_regul_pieceJoint01");

  initializeFileHandlers("1", fileInput1);


  const buttons = document.querySelectorAll('.tabs button');
    const contents = document.querySelectorAll('.tab-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            // Supprime la classe active
            buttons.forEach(btn => btn.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));

            // Ajoute la classe active au bouton et au contenu associé
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });