import { FetchManager } from '../api/FetchManager';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

document.addEventListener('DOMContentLoaded', (event) => {
  /**
   * CACHE ET AFFICHE (nom, prenom, cin) SELON LE SALARIE (Temporaire ou permanant)
   */
  const nom = document.querySelector('#dom_form1_nom');
  const prenom = document.querySelector('#dom_form1_prenom');
  const cin = document.querySelector('#dom_form1_cin');
  const salarier = document.querySelector('#dom_form1_salarie');
  const matriculeNomInput = document.querySelector('#dom_form1_matriculeNom');
  const matriculeInput = document.querySelector('#dom_form1_matricule');

  function toggleFields() {
    if (salarier.value === 'TEMPORAIRE') {
      nom.parentElement.style.display = 'block';
      prenom.parentElement.style.display = 'block';
      cin.parentElement.style.display = 'block';
      nom.disabled = false;
      prenom.disabled = false;
      cin.disabled = false;
      matriculeNomInput.parentElement.style.display = 'none';
      matriculeInput.parentElement.style.display = 'none';
      matriculeNomInput.disabled = true;
      matriculeInput.disabled = true;
    } else {
      nom.parentElement.style.display = 'none';
      prenom.parentElement.style.display = 'none';
      cin.parentElement.style.display = 'none';
      nom.disabled = true;
      prenom.disabled = true;
      cin.disabled = true;
      matriculeNomInput.parentElement.style.display = 'block';
      matriculeInput.parentElement.style.display = 'block';
      matriculeNomInput.disabled = false;
      matriculeInput.disabled = false;
    }
  }

  salarier.addEventListener('change', toggleFields);
  toggleFields();

  /**
   * AFFICHE champ CATEGORIE selon le TYPE DE MISSION
   */
  const sousTypeDocument = document.querySelector(
    '#dom_form1_sousTypeDocument'
  );
  const agenceInput = document.querySelector('#dom_form1_agenceEmetteur');

  const categorie = document.querySelector('#dom_form1_categorie');

  sousTypeDocument.addEventListener('change', changementSelon);

  function changementSelon() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    const codeAgence = agenceInput.value.split(' ')[0];
    console.log(sousTypeDocumentValue);
    console.log(codeAgence);
    if (
      sousTypeDocumentValue !== '5' &&
      sousTypeDocumentValue !== '2' &&
      codeAgence !== '50'
    ) {
      categorie.parentElement.style.display = 'none';
    } else if (sousTypeDocumentValue !== '2' && codeAgence === '50') {
      categorie.parentElement.style.display = 'none';
    } else {
      categorie.parentElement.style.display = 'block';
      selectCategorie();
    }
  }

  function selectCategorie() {
    const sousTypeDocumentValue = sousTypeDocument.value;
    let url = `categorie-fetch/${sousTypeDocumentValue}`;
    fetchManager
      .get(url)
      .then((categories) => {
        console.log(categories);

        //Supprimer toutes les options existantes
        while (categorie.options.length > 0) {
          categorie.remove(0);
        }

        //Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < categories.length; i++) {
          var option = document.createElement('option');
          option.value = categories[i].id;
          option.text = categories[i].description;
          categorie.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < categorie.options.length; i++) {
          var option = categorie.options[i];
          console.log('Value: ' + option.value + ', Text: ' + option.text);
        }
      })
      .catch((error) => console.error('Error:', error));
  }

  /**
   * AFFICHER LE MATRICULE SELON le Matricule et Nom Choisie
   *
   */

  $('#dom_form1_matriculeNom').select2({
    width: '100%', // Optionnel : ajustez la largeur selon vos besoins
    placeholder: '-- choisir une personnel --',
  });

  // Ajouter un écouteur d'événement pour Select2
  $('#dom_form1_matriculeNom').on('select2:select', function (e) {
    console.log('Okey');
    let matriculeNom = $('#dom_form1_matriculeNom option:selected').text();
    let matricule = matriculeNom.slice(0, 4);
    console.log(matricule);
    matriculeInput.value = matricule;
    //changeMatricule(e.params.data.id); // Passer l'id sélectionné à la fonction
  });

  /** CHANGER LE CHAMP CATEGORIE SELON LES CRITERES */
});
