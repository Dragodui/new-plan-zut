class Schedule {
  constructor() {
    this.api = axios.create({
      baseURL: "http://localhost:8000",
    });
    this.grid = "dayGridMonth";
    this.events = [];
    this.getSchedule = this.getSchedule.bind(this);
    this.calendar = null;
    this.classroom = null;
    this.classrooms = [];
    this.kind = null;
    this.subject = null;
    this.teacher = null;
    this.number = null;

    document
      .getElementById("scheduleForm")
      .addEventListener("submit", this.getSchedule);
    document
      .getElementById("dayViewButton")
      .addEventListener("click", () => this.changeGrid("timeGridDay"));
    document
      .getElementById("weekViewButton")
      .addEventListener("click", () => this.changeGrid("dayGridWeek"));
    document
      .getElementById("monthViewButton")
      .addEventListener("click", () => this.changeGrid("dayGridMonth"));
    document
      .getElementById("classroom")
      .addEventListener("change", this.getClassroom.bind(this));
    document
      .getElementById("subject")
      .addEventListener("change", this.getSubject.bind(this));
    document
      .getElementById("teacher")
      .addEventListener("change", this.getTeacher.bind(this));
  }

  async getTeacher() {
    const teacherQuery = document.getElementById("teacher").value;
    const resultsContainer = document.getElementById("teacher-results");
    resultsContainer.innerHTML = "";

    if (!teacherQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/teacher?teacher=${teacherQuery}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        resultsContainer.innerHTML = "<p>No teacher found.</p>";
        return;
      }

      const ul = document.createElement("ul");
      ul.classList.add("teacher-list");

      data
        .filter((item) => item && typeof item === "object" && item.item)
        .forEach((subject) => {
          const li = document.createElement("li");
          li.textContent = subject.item;
          li.addEventListener("click", () => this.selectTeacher(subject.item));
          ul.appendChild(li);
        });

      resultsContainer.appendChild(ul);
    } catch (error) {
      resultsContainer.innerHTML =
        "<p style='color: red;'>Error loading teachers.</p>";
    }
  }

  selectTeacher(teacherName) {
    this.teacher = teacherName;
    document.getElementById("teacher").value = teacherName;
    document.getElementById("teacher-results").innerHTML = "";
  }

  async getSubject() {
    const subjectQuery = document.getElementById("subject").value;
    const resultsContainer = document.getElementById("subject-results");
    resultsContainer.innerHTML = "";

    if (!subjectQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/subject?subject=${subjectQuery}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        resultsContainer.innerHTML = "<p>No subjects found.</p>";
        return;
      }

      const ul = document.createElement("ul");
      ul.classList.add("subject-list");

      data
        .filter((item) => item && typeof item === "object" && item.item)
        .forEach((subject) => {
          const li = document.createElement("li");
          li.textContent = subject.item;
          li.addEventListener("click", () => this.selectSubject(subject.item));
          ul.appendChild(li);
        });

      resultsContainer.appendChild(ul);
    } catch (error) {
      resultsContainer.innerHTML =
        "<p style='color: red;'>Error loading subjects.</p>";
    }
  }

  selectSubject(subjectName) {
    this.subject = subjectName;
    document.getElementById("subject").value = subjectName;
    document.getElementById("subject-results").innerHTML = "";
  }

  async getClassroom() {
    const classroomQuery = document.getElementById("classroom").value;
    const resultsContainer = document.getElementById("classroom-results");
    resultsContainer.innerHTML = "";

    if (!classroomQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/classroom?room=${classroomQuery}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        resultsContainer.innerHTML = "<p>No classrooms found.</p>";
        return;
      }

      const ul = document.createElement("ul");
      ul.classList.add("classroom-list");

      data
        .filter((item) => item && typeof item === "object" && item.item)
        .forEach((classroom) => {
          const li = document.createElement("li");
          li.textContent = classroom.item;
          li.addEventListener("click", () =>
            this.selectClassroom(classroom.item)
          );
          ul.appendChild(li);
        });

      resultsContainer.appendChild(ul);
    } catch (error) {
      resultsContainer.innerHTML =
        "<p style='color: red;'>Error loading classrooms.</p>";
    }
  }

  selectClassroom(classroomName) {
    this.classroom = classroomName;
    document.getElementById("classroom").value = classroomName;
    document.getElementById("classroom-results").innerHTML = "";
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
    this.number = studentNumber;
    const resultDiv = document.getElementById("result");

    resultDiv.innerHTML = "Loading...";

    try {
      const queryParams = new URLSearchParams(window.location.search);
      if (studentNumber) {
        queryParams.set("number", studentNumber);
      }

      if (this.classroom) {
        queryParams.set("room", this.classroom);
      }

      if (this.subject) {
        queryParams.set("subject", this.subject);
      }

      if (this.teacher) {
        queryParams.set("teacher", this.teacher);
      }

      const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
      history.pushState({}, "", newUrl);

      const response = await this.api.get(
        `/schedule?${queryParams.toString()}`
      );

      if (response.status !== 200) {
        throw new Error("Failed to fetch schedule");
      }

      const data = response.data;

      this.events = data.map((item) => ({
        title: item.subject,
        start: item.start,
        end: item.end,
        description: item.description || "No description",
        workerTitle: item.worker_title,
        worker: item.worker,
        room: item.room,
        groupName: item.group_name,
        tokName: item.tok_name,
        lessonForm: item.lesson_form,
        lessonFormShort: item.lesson_form_short,
        lesson_status: item.lesson_status,
        color: item.color,
      }));

      if (!this.calendar) {
        await this.initializeCalendar();
      } else {
        this.updateCalendar();
      }
      this.createLegend();
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
    this.teacher = null;
    this.subject = null;
    this.classroom = null;
    this.number = null;
    try {
      const calendarElement = document.getElementById("calendar");
      const eventInfoContainer = document.getElementById("event-info");
      this.calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: this.grid,
        events: this.events,
        eventClick: function (info) {
            eventInfoContainer.innerHTML = "";
            const title = document.createElement("p");
            title.style.fontWeight = "bold";
            title.innerHTML = `${info.event.title}`;
            eventInfoContainer.appendChild(title);
    
            const room = document.createElement("p");
            room.innerHTML = `<strong>Classroom:</strong> ${
              info.event.extendedProps.room || "No info"
            }`;
            eventInfoContainer.appendChild(room);
    
            const time = document.createElement("p");
            time.innerHTML = `<strong>Time:</strong> ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}`;
            eventInfoContainer.appendChild(time);
    
            const teacher = document.createElement("p");
            teacher.innerHTML = `<strong>Teacher:</strong> ${
              info.event.extendedProps.workerTitle || "No info"
            }`;
            eventInfoContainer.appendChild(teacher);
    
            const description = document.createElement("p");
            description.innerHTML = `<strong>Description:</strong> ${
              info.event.extendedProps.description || "No info"
            }`;
            eventInfoContainer.appendChild(description);
            eventInfoContainer.style.display = "block";
            eventInfoContainer.scrollIntoView({ behavior: "smooth" });
          },
      });

      await this.calendar.render();
    } catch (error) {
      console.error(error);
    }
  }

  createLegend() {
    const legendContainer = document.getElementById("legend");
    legendContainer.innerHTML = "";

    const forms = new Map();

    this.events.forEach((event) => {
      if (event.lessonForm && event.color) {
        forms.set(event.lessonForm, event.color);
      }
    });

    forms.forEach((color, form) => {
      const legendItem = document.createElement("div");
      legendItem.style.display = "flex";
      legendItem.style.alignItems = "center";
      legendItem.style.marginBottom = "5px";

      const colorBox = document.createElement("div");
      colorBox.style.width = "20px";
      colorBox.style.height = "20px";
      colorBox.style.backgroundColor = color;
      colorBox.style.marginRight = "10px";
      legendItem.appendChild(colorBox);

      const label = document.createElement("span");
      label.textContent = form;
      legendItem.appendChild(label);

      legendContainer.appendChild(legendItem);
    });
  }
}
