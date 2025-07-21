import { displayOverlay } from "../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
    const semaineInput = document.getElementById("planning_atelier_search_numeroSemaine");
    const dateDebutInput = document.getElementById("planning_atelier_search_dateDebut");
    const dateFinInput = document.getElementById("planning_atelier_search_dateFin");

    function getStartAndEndDate(week, year) {
        const simple = new Date(year, 0, 1 + (week - 1) * 7);
        const dow = simple.getDay();
        const ISOweekStart = simple;
        if (dow <= 4)
            ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
        else
            ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());

        const start = new Date(ISOweekStart);
        const end = new Date(ISOweekStart);
        end.setDate(start.getDate() + 6);

        return {
            start: start.toISOString().split("T")[0],
            end: end.toISOString().split("T")[0],
        };
    }
      function getMondayOfISOWeek(week, year) {
        const simple = new Date(Date.UTC(year, 0, 1 + (week - 1) * 7));
        const dayOfWeek = simple.getUTCDay();
        const ISOweekStart = new Date(simple);
        const diffToMonday = (dayOfWeek <= 4 ? dayOfWeek - 1 : dayOfWeek - 8);
        ISOweekStart.setUTCDate(simple.getUTCDate() - diffToMonday);
        return ISOweekStart;
    }

    function formatDate(date) {
        return date.toISOString().split("T")[0];
    }
function getStartAndEndDateISO(week, year) {
        const jan4 = new Date(year, 0, 4); // 4 janvier
        const jan4Day = jan4.getDay(); // 0 (dim) à 6 (sam)

        const dayOffset = (jan4Day <= 4 ? jan4Day - 1 : jan4Day - 8);
        const mondayOfWeek1 = new Date(jan4);
        mondayOfWeek1.setDate(jan4.getDate() - dayOffset);

        const mondayOfTargetWeek = new Date(mondayOfWeek1);
        mondayOfTargetWeek.setDate(mondayOfWeek1.getDate() + (week - 1) * 7);

        const sundayOfTargetWeek = new Date(mondayOfTargetWeek);
        sundayOfTargetWeek.setDate(mondayOfTargetWeek.getDate() + 6);

        return {
            start: mondayOfTargetWeek.toISOString().split("T")[0],
            end: sundayOfTargetWeek.toISOString().split("T")[0],
        };
    }
    // if (semaineInput) {
    //     semaineInput.addEventListener("change", function () {
    //         const week = parseInt(this.value, 10);
    //         const year = new Date().getFullYear();
    //         if (week >= 1 && week <= 53) {
    //             const dates = getStartAndEndDateISO(week, year);
    //             dateDebutInput.value = dates.start;
    //             dateFinInput.value = dates.end;
    //         }
    //     });
    // }
    function eventSurSemaineInput(semaineInput) {
        const week = parseInt(semaineInput.value, 10);
        const year = new Date().getFullYear(); // ou un champ personnalisé
        if (week >= 1 && week <= 53) {
            const monday = getMondayOfISOWeek(week, year);
            const sunday = new Date(monday);
            sunday.setUTCDate(monday.getUTCDate() + 6);
    
            dateDebutInput.value = formatDate(monday);
            dateFinInput.value = formatDate(sunday);
        }
    }

     if (semaineInput) {
        eventSurSemaineInput(semaineInput);
        semaineInput.addEventListener("change", function () {
            eventSurSemaineInput(semaineInput);
        });
    }
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

