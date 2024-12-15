
document.addEventListener("DOMContentLoaded", async () => {
  window.history.replaceState({}, '', window.location.pathname);
  const schedule = new Schedule();
  await schedule.initializeCalendar(); 
});
