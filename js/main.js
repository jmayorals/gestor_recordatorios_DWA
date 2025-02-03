// js/main.js
function openTab(evt, tabId) {
    evt.preventDefault();
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(el => el.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
  
    // Si abrimos el calendario, renderizar FullCalendar si no está
    if (tabId === 'tab-calendario') {
      initCalendar(); 
    }
  }
  
  // Variable para controlar si ya hemos inicializado el calendario
  let calendarInitialized = false;
  
  function initCalendar() {
    if (calendarInitialized) return;
    calendarInitialized = true;
  
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: 'calendar_events.php',  // llama al PHP que retorna JSON
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listMonth'
      }
    });
    calendar.render();
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    // Por defecto, muestra la pestaña 'lista'
    document.getElementById('tab-lista').style.display = 'block';
  });
  