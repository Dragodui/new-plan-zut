
document.addEventListener("DOMContentLoaded", async () => {
  const schedule = new Schedule();
  schedule.parseUrlParams();
  await schedule.initializeCalendar(); 
});
