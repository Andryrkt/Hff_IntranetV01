import { acceptReplanification } from './calendarModule/eventDropModule';
import {
  afficherDateReplanification,
  formatDatePartielDate,
} from './calendarModule/formatDateModule';

document.addEventListener('DOMContentLoaded', function () {
  const eventModalEl = document.getElementById('eventModal');
  const eventModal = new bootstrap.Modal(eventModalEl);

  const replanificationModal = new bootstrap.Modal('#replanificationModal'); // création de modal avec bootstrap avec l'id de l'élément
  const notificationToast = new bootstrap.Toast('#notificationToast'); // création de toast de notification

  // Détail d'un ticket
  const numeroTicket = document.getElementById('numeroTicket');
  const objetDemande = document.getElementById('objetDemande');
  const detailDemande = document.getElementById('detailDemande');
  const demandeur = document.getElementById('demandeur');
  const intervenant = document.getElementById('intervenant');
  const dateCreation = document.getElementById('dateCreation');
  const dateFinSouhaite = document.getElementById('dateFinSouhaite');
  const categorie = document.getElementById('categorie');
  const datePlanification = document.getElementById('datePlanification');
  const debutPlanning = document.getElementById('debutPlanning');
  const finPlanning = document.getElementById('finPlanning');
  const linkDetail = document.getElementById('linkDetail');

  // contenant du texte de la notification
  const notification = document.getElementById('toast-notification-content');

  // contenant du texte du modal de replanification
  const dateReplanification = document.getElementById(
    'date-replanification-content'
  );

  var calendarEl = document.getElementById('calendar');
  var spinner = document.getElementById('loading-spinner-overlay');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'fr',
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
    },
    views: {
      dayGridMonth: {
        dayHeaderFormat: { weekday: 'long' }, // Affichage du jour en texte complet
        titleFormat: { year: 'numeric', month: 'long' }, // Format du titre
        dayMaxEvents: true, // Afficher "+X more" si trop d'événements
      },
      timeGridWeek: {
        titleFormat: { day: 'numeric', month: 'long', year: 'numeric' }, // Format du titre
        dayHeaderFormat: { weekday: 'long', day: '2-digit', month: '2-digit' }, // Affichage du jour en texte complet
        slotDuration: '00:15:00', // Durée des créneaux
        slotLabelFormat: { hour: 'numeric', minute: '2-digit', hour12: false }, // Format des heures
        allDaySlot: false, // Désactiver la ligne "Toute la journée"
        nowIndicator: true, // Indicateur de l'heure actuelle
      },
      timeGridDay: {
        slotDuration: '00:15:00', // Créneaux plus courts
        scrollTime: '08:00:00', // Scroll automatique à 08h00
        allDaySlot: true, // Activer la ligne "Toute la journée"
        nowIndicator: true,
      },
    },
    buttonText: {
      today: "Aujourd'hui",
      month: 'Mois',
      week: 'Semaine',
      day: 'Jour',
      list: 'Liste mensuel',
    },
    events: '/Hffintranet/api/tik/calendar-fetch',
    editable: false,
    selectable: true,
    loading: function (isLoading) {
      if (isLoading) {
        spinner.classList.remove('d-none'); // Affiche le spinner
      } else {
        spinner.classList.add('d-none'); // Cache le spinner
      }
    },
    select: function (info) {
      document.getElementById('calendar_dateDebutPlanning').value =
        info.startStr;
      document.getElementById('calendar_dateFinPlanning').value = info.endStr;
      // Afficher le modal
      eventModal.show();
    },
    eventClick: function (info) {
      // donnée de extendedProps provenant l'API
      const data = info.event.extendedProps;

      numeroTicket.innerHTML = data.numeroTicket;
      objetDemande.innerHTML = data.objetDemande;
      detailDemande.innerHTML = data.detailDemande;
      demandeur.innerHTML = data.demandeur;
      intervenant.innerHTML = data.intervenant;
      dateCreation.innerHTML = data.dateCreation;
      dateFinSouhaite.innerHTML = data.dateFinSouhaite;
      categorie.innerHTML = data.categorie;
      datePlanification.innerHTML = formatDatePartielDate(info.event.start); // formater une date en d/m/Y
      debutPlanning.innerHTML = data.debutPlanning;
      finPlanning.innerHTML = data.finPlanning;

      let id = data.id;
      linkDetail.href = linkDetail.href.replace(/\/[^/]*$/, `/${id}`);

      // Afficher le modal
      eventModal.show();
    },
    eventDrop: function (info) {
      // texte pour le modal
      dateReplanification.innerHTML = afficherDateReplanification(
        info.event.start,
        info.event.end
      );

      // afficher modal
      replanificationModal.show();

      // Confirmation
      const non = document.getElementById('refuseReplanification');
      const oui = document.getElementById('confirmReplanification');
      document
        .getElementById('replanificationModal')
        .addEventListener('hidden.bs.modal', function () {
          info.revert();
          notification.innerHTML = `<strong>Annulation effectuée.</strong> La demande de <strong>replanification</strong> a bien été annulée.`;
          notificationToast.show();
        });
      non.addEventListener('click', function () {
        info.revert();
        notification.innerHTML = `<strong>Annulation effectuée.</strong> La demande de <strong>replanification</strong> a bien été annulée.`;
        notificationToast.show();
      });
      oui.addEventListener('click', function () {
        acceptReplanification(
          spinner,
          `/Hffintranet/api/tik/data/calendar/${info.event.id}`,
          {
            dateDebut: info.event.startStr,
            dateFin: info.event.endStr,
          }
        );
      });
    },
  });

  calendar.render();

  document
    .getElementById('eventForm')
    ?.addEventListener('submit', function (e) {
      e.preventDefault();

      const title = document.getElementById('calendar_objetDemande').value;
      const description = document.getElementById(
        'calendar_detailDemande'
      ).value;
      const start = document.getElementById('calendar_dateDebutPlanning').value;
      const end = document.getElementById('calendar_dateFinPlanning').value;

      fetch('/Hffintranet/api/tik/calendar-fetch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, description, start, end }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          response.json();
        })
        .then((data) => {
          console.log(data);

          alert('Événement ajouté avec succès !');
          calendar.refetchEvents();

          // Réinitialiser le formulaire et masquer le modal
          document.getElementById('eventForm').reset();
          eventModal.hide();
        });
    });
});
