document.addEventListener('DOMContentLoaded', function () {
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
    loading: function (isLoading) {
      console.log(spinner.classList);
      if (isLoading) {
        spinner.classList.remove('d-none'); // Affiche le spinner
      } else {
        spinner.classList.add('d-none'); // Cache le spinner
      }
    },
    editable: false,
    selectable: true,
    select: function (info) {
      document.getElementById('calendar_dateDebutPlanning').value =
        info.startStr;
      document.getElementById('calendar_dateFinPlanning').value = info.endStr;

      // Afficher le modal
      const eventModal = new bootstrap.Modal(
        document.getElementById('eventModal')
      );
      eventModal.show();
    },
    eventClick: function (info) {
      alert('Événement : ' + info.event.title);
    },
  });

  calendar.render();

  document.getElementById('eventForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const title = document.getElementById('calendar_objetDemande').value;
    const description = document.getElementById('calendar_detailDemande').value;
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
        const eventModal = bootstrap.Modal.getInstance(
          document.getElementById('eventModal')
        );
        eventModal.hide();
      });
  });

  /*
DATE de debut et date de fin
*/
  // flatpickr(".datetime-picker", {
  //   enableTime: true,
  //   dateFormat: "Y-m-d H:i",
  // });
});
