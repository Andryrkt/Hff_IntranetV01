document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("calendar");
  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: "fr",
    initialView: "dayGridMonth",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
    },
    buttonText: {
      today: "Aujourd'hui",
      month: "Mois",
      week: "Semaine",
      day: "Jour",
      list: "Planning",
    },
    events: "/agendat/api.php",
    editable: true,
    selectable: true,
    select: function (info) {
      document.getElementById("calendar_dateDebutPlanning").value =
        info.startStr;
      document.getElementById("calendar_dateFinPlanning").value = info.endStr;

      // Afficher le modal
      const eventModal = new bootstrap.Modal(
        document.getElementById("eventModal")
      );
      eventModal.show();
    },
    eventClick: function (info) {
      alert("Événement : " + info.event.title);
    },
  });

  calendar.render();

  document.getElementById("eventForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const title = document.getElementById("calendar_objetDemande").value;
    const description = document.getElementById("calendar_detailDemande").value;
    const start = document.getElementById("calendar_dateDebutPlanning").value;
    const end = document.getElementById("calendar_dateFinPlanning").value;

    fetch("/agendat/api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ title, description, start, end }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Événement ajouté avec succès !");
          calendar.refetchEvents();

          // Réinitialiser le formulaire et masquer le modal
          document.getElementById("eventForm").reset();
          const eventModal = bootstrap.Modal.getInstance(
            document.getElementById("eventModal")
          );
          eventModal.hide();
        } else {
          alert("Erreur lors de l'ajout de l'événement.");
        }
      });
  });

  /*
DATE de debut et date de fin
*/
  flatpickr(".datetime-picker", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
  });
});
