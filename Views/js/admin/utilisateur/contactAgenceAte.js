import { FetchManager } from '../../api/FetchManager';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

/**
 * SELECTE 2/ permet de faire une recherche sur le select
 */
document.addEventListener('DOMContentLoaded', function () {
  $('.selecteur2').select2({
    allowClear: true,
    placeholder: 'Sélectionnez une option',
    theme: 'bootstrap',
  });

  let isProgrammaticChange = false;

  $('#contact_agence_ate_matricule').on('change', function () {
    if (isProgrammaticChange) {
      // Si c'est un changement déclenché par le script, on arrête ici
      isProgrammaticChange = false; // Réinitialiser le drapeau
      return;
    }

    const id = $(this).val();
    console.log(id);

    if (id) {
      // Faire une requête AJAX pour récupérer les informations
      fetchManager
        .get(`api/contact-agence-ate/${id}`)
        .then((data) => {
          console.log(data);

          // Définir le drapeau pour éviter un événement infini
          isProgrammaticChange = true;

          // Mettre à jour les champs et déclencher le changement
          $('#contact_agence_ate_nom').val(data.id).trigger('change');
          $('#contact_agence_ate_email').val(data.id).trigger('change');
          $('#contact_agence_ate_prenom').val(data.prenom);
          $('#contact_agence_ate_telephone').val('+261' + data.telephone);
        })
        .catch((error) => {
          console.error('Erreur : ', error);
        });
    } else {
      // Réinitialiser les champs si aucune matricule n'est sélectionnée
      $('#contact_agence_ate_nom').val('');
      $('#contact_agence_ate_email').val('');
      $('#contact_agence_ate_prenom').val('');
      $('#contact_agence_ate_telephone').val('');
    }
  });

  /** Changer le NOm */
  $('#contact_agence_ate_nom').on('change', function () {
    if (isProgrammaticChange) {
      // Si c'est un changement déclenché par le script, on arrête ici
      isProgrammaticChange = false; // Réinitialiser le drapeau
      return;
    }

    const id = $(this).val();
    console.log(id);

    if (id) {
      // Faire une requête AJAX pour récupérer les informations
      fetchManager
        .get(`api/contact-agence-ate/${id}`)
        .then((data) => {
          console.log(data);

          // Définir le drapeau pour éviter un événement infini
          isProgrammaticChange = true;

          // Mettre à jour les champs et déclencher le changement

          $('#contact_agence_ate_matricule').val(data.id).trigger('change');
          $('#contact_agence_ate_email').val(data.id).trigger('change');
          $('#contact_agence_ate_prenom').val(data.prenom);
          $('#contact_agence_ate_telephone').val('+261' + data.telephone);
        })
        .catch((error) => {
          console.error('Erreur : ', error);
        });
    } else {
      // Réinitialiser les champs si aucune matricule n'est sélectionnée
      $('#contact_agence_ate_matricule').val('');
      $('#contact_agence_ate_email').val('');
      $('#contact_agence_ate_prenom').val('');
      $('#contact_agence_ate_telephone').val('');
    }
  });

  /** Changer le mail */
  $('#contact_agence_ate_email').on('change', function () {
    if (isProgrammaticChange) {
      // Si c'est un changement déclenché par le script, on arrête ici
      isProgrammaticChange = false; // Réinitialiser le drapeau
      return;
    }

    const id = $(this).val();
    console.log(id);

    if (id) {
      // Faire une requête AJAX pour récupérer les informations
      fetchManager
        .get(`api/contact-agence-ate/${id}`)
        .then((data) => {
          console.log(data);

          // Définir le drapeau pour éviter un événement infini
          isProgrammaticChange = true;

          // Mettre à jour les champs et déclencher le changement

          $('#contact_agence_ate_matricule').val(data.id).trigger('change');
          $('#contact_agence_ate_nom').val(data.id).trigger('change');
          $('#contact_agence_ate_prenom').val(data.prenom);
          $('#contact_agence_ate_telephone').val('+261' + data.telephone);
        })
        .catch((error) => {
          console.error('Erreur : ', error);
        });
    } else {
      // Réinitialiser les champs si aucune matricule n'est sélectionnée
      $('#contact_agence_ate_matricule').val('');
      $('#contact_agence_ate_nom').val('');
      $('#contact_agence_ate_prenom').val('');
      $('#contact_agence_ate_telephone').val('');
    }
  });
});
