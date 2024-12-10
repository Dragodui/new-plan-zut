class Schedule {
  constructor() {
    this.api = axios.create({
      baseURL: "http://localhost:8000",
    });
    this.grid = 'dayGridMonth';
    this.events = [];
    this.getSchedule = this.getSchedule.bind(this);
    this.calendar = null;

    document
      .getElementById("scheduleForm")
      .addEventListener("submit", this.getSchedule);
    
    document.getElementById("dayViewButton").addEventListener("click", () => this.changeGrid('timeGridDay'));
    document.getElementById("weekViewButton").addEventListener("click", () => this.changeGrid('dayGridWeek'));
    document.getElementById("monthViewButton").addEventListener("click", () => this.changeGrid('dayGridMonth'));
  }

  async changeGrid(view) {
    this.grid = view;
    if (this.calendar) {
      await this.calendar.changeView(view);
    }
  }

  async getSchedule(e) {
    e.preventDefault(); 

    const studentNumber = document.getElementById("studentNumber").value;
    const resultDiv = document.getElementById("result");

    resultDiv.innerHTML = "Loading..."; 

    try {
      const response = await this.api.get(
        `/schedule?number=${encodeURIComponent(studentNumber)}`
      );

      if (response.status !== 200) {
        throw new Error("Failed to fetch schedule");
      }

      const data = response.data;

      this.events = data.map(item => ({
        title: item.subject, 
        start: item.start,    
        end: item.end,
        description: item.description || 'No description',
      }));

      if (!this.calendar) {
        await this.initializeCalendar(); 
      } else {
        this.updateCalendar(); 
      }

      // resultDiv.innerHTML = '';

    } catch (error) {
      resultDiv.innerHTML = `<span style="color: red;">Error: ${error.message}</span>`;
    }
  }

  async updateCalendar() {
    try {
      this.calendar.removeAllEvents();
      this.calendar.addEventSource(this.events);
      this.calendar.render();
    } catch (error) {
      console.error(error);
    }
  }

  async initializeCalendar() {
    try {
      const calendarElement = document.getElementById("calendar");
      this.calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: this.grid,
        events: this.events,
        eventClick: function(info) {
          alert(info.event.title + ': ' + info.event.extendedProps.description);
        }
      });
      
      await this.calendar.render();
    } catch (error) {
      console.error(error);
    }
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  const schedule = new Schedule();
  await schedule.initializeCalendar(); 
});
