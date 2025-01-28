class Schedule {
  api: typeof axios.AxiosInstance;
  grid: string;
  events: Event[];
  calendar: typeof FullCalendar.Calendar | null;
  classroom: string | null;
  kind: string | null;
  subject: string | null;
  teacher: string | null;
  number: string | null;
  startDate: Date | null;
  endDate: Date | null;
  building: string | null;
  shouldFetchSchedule: boolean;

  constructor() {
    this.api = axios.create({
      baseURL: "http://localhost:8000",
    });
    this.shouldFetchSchedule = true;
    this.grid = "dayGridMonth";
    this.events = [];
    this.getSchedule = this.getSchedule.bind(this);
    this.calendar = null;
    this.classroom = null;
    this.building = null;
    this.kind = null;
    this.subject = null;
    this.teacher = null;
    this.number = null;
    this.startDate = null;
    this.endDate = null;
    this.getClassroom = this.debounce(this.getClassroom.bind(this), 1000);
    this.getSubject = this.debounce(this.getSubject.bind(this), 1000);
    this.getTeacher = this.debounce(this.getTeacher.bind(this), 1000);

    this.setupEventListeners();

    if (this.hasValidParams() !== null) {
      this.getSchedule();
    }
  }
  debounce(func: Function, delay: number) {
    let timeoutId: number;
    return async (): Promise<void> => {
      clearTimeout(timeoutId); 
      timeoutId = setTimeout(() => {
        func.apply(this); 
      }, delay);
    };
  }

  setupEventListeners() {
    document.getElementById("scheduleForm")?.addEventListener("submit", (e) => {
      e.preventDefault();
      if(this.hasValidParams() !== null) {
        this.getSchedule();
      }
    });
    document.getElementById("dayViewButton")?.addEventListener("click", () => this.changeGrid("timeGridDay"));
    document.getElementById("weekViewButton")?.addEventListener("click", () => this.changeGrid("dayGridWeek"));
    document.getElementById("monthViewButton")?.addEventListener("click", () => this.changeGrid("dayGridMonth"));

    const buildingInput = document.getElementById("building");
    if (buildingInput) {
      buildingInput.addEventListener("input", (e) => {
        const value = (e.target as HTMLInputElement).value;
        if (!value) this.selectBuilding(null);
        else this.getBuilding();
      });
    }

    const classroomInput = document.getElementById("classroom");
    if (classroomInput) {
      classroomInput.addEventListener("input", (e) => {
        const value = (e.target as HTMLInputElement).value;
        if (!value) this.selectClassroom(null);
        else this.getClassroom();
      });
    }

    const subjectInput = document.getElementById("subject");
    if (subjectInput) {
      subjectInput.addEventListener("input", (e) => {
        const value = (e.target as HTMLInputElement).value;
        if (!value) this.selectSubject(null);
        else this.getSubject();
      });
    }

    const teacherInput = document.getElementById("teacher");
    if (teacherInput) {
      teacherInput.addEventListener("input", (e) => {
        const value = (e.target as HTMLInputElement).value;
        if (!value) this.selectTeacher(null);
        else this.getTeacher();
      });
    }

    const studentNumberInput = document.getElementById("studentNumber");
    if (studentNumberInput) {
      studentNumberInput.addEventListener("input", (e) => {
        const value = (e.target as HTMLInputElement).value;
        this.number = value || null;
        this.updateQueryParams();
      });
    }
  }

  parseUrlParams() {
    const params = new URLSearchParams(window.location.search);
    this.number = params.get("number") || null;
    this.classroom = params.get("room") || null;
    this.subject = params.get("subject") || null;
    this.teacher = params.get("teacher") || null;
    this.building = params.get("building") || null;

    if (this.number)
      (document.getElementById("studentNumber") as HTMLInputElement).value = this.number;
    if (this.classroom)
      (document.getElementById("classroom")as HTMLInputElement).value = this.classroom;
    if (this.subject) (document.getElementById("subject") as HTMLInputElement).value = this.subject;
    if (this.teacher) (document.getElementById("teacher") as HTMLInputElement).value = this.teacher;
    if (this.building) (document.getElementById("building") as HTMLInputElement).value = this.building;
  }

  async getTeacher() {
    const teacherQuery = (document.getElementById("teacher") as HTMLInputElement).value;
    const resultsContainer = document.getElementById("teacher-results");
    if (resultsContainer) (resultsContainer as HTMLDivElement).innerHTML = "";

    if (!teacherQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/teacher?teacher=${teacherQuery}`);
      const data = response.data;

      if ((!Array.isArray(data) || data.length === 0) && resultsContainer) {
        (resultsContainer as HTMLDivElement).innerHTML = "<p>No teacher found.</p>";
        return;
      }

      const ul = document.createElement("ul");
      ul.classList.add("teacher-list");
      data
        .filter((item: any) => item && typeof item === "object" && item.item)
        .forEach((subject: any) => {
          const li = document.createElement("li");
          li.textContent = subject.item;
          li.addEventListener("click", () => this.selectTeacher(subject.item));
          ul.appendChild(li);
        });

      if (resultsContainer) {
        (resultsContainer as HTMLDivElement).style.display = "block";
        (resultsContainer as HTMLDivElement).appendChild(ul)
      };
    } catch (error) {
      if (resultsContainer) (resultsContainer as HTMLDivElement).innerHTML = "<p style='color: red;'>Error loading teachers.</p>";
    }
  }

  selectTeacher(teacherName: string | null) {
    (document.getElementById("teacher-results") as HTMLDivElement).style.display = "none";
    this.teacher = teacherName || null;
    const teacherInput = document.getElementById("teacher");
    if (teacherInput) (teacherInput as HTMLInputElement).value = teacherName || "";

    const resultsContainer = document.getElementById("teacher-results");
    if (resultsContainer) resultsContainer.innerHTML = "";

    this.updateQueryParams();
  }

  async getSubject() {
    const subjectQuery = (document.getElementById("subject")as HTMLInputElement).value;
    const resultsContainer = document.getElementById("subject-results");
    (resultsContainer as HTMLDivElement).innerHTML = "";

    if (!subjectQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/subject?subject=${subjectQuery}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        (resultsContainer as HTMLDivElement).innerHTML = "<p>No subjects found.</p>";
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

        if (resultsContainer) {
          (resultsContainer as HTMLDivElement).style.display = "block";
          (resultsContainer as HTMLDivElement).appendChild(ul)
        };
    } catch (error) {
      (resultsContainer as HTMLDivElement).innerHTML =
        "<p style='color: red;'>Error loading subjects.</p>";
    }
  }

  selectSubject(subjectName: string | null) {
    (document.getElementById("subject-results") as HTMLDivElement).style.display = "none";
    this.subject = subjectName || null;
    const subjectInput = document.getElementById("subject");
    if (subjectInput) (subjectInput as HTMLInputElement).value = subjectName || "";

    const resultsContainer = document.getElementById("subject-results");
    if (resultsContainer) resultsContainer.innerHTML = "";

    this.updateQueryParams();
  }

  async getBuilding() {
    const buildingQuery = (document.getElementById("building")as HTMLInputElement).value;
    const resultsContainer = document.getElementById("building-results");
    (resultsContainer as HTMLDivElement).innerHTML = "";

    if (!buildingQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/building?building=${buildingQuery}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        (resultsContainer as HTMLDivElement).innerHTML = "<p>No buildings found.</p>";
        return;
      }

      const ul = document.createElement("ul");
      ul.classList.add("building-list");

      data
        .filter((item) => item && typeof item === "object" && item.item)
        .forEach((building) => {
          const li = document.createElement("li");
          li.textContent = building.item;
          li.addEventListener("click", () => this.selectBuilding(building.item));
          ul.appendChild(li);
        });

        if (resultsContainer) {
          (resultsContainer as HTMLDivElement).style.display = "block";
          (resultsContainer as HTMLDivElement).appendChild(ul)
        };
    } catch (error) {
      (resultsContainer as HTMLDivElement).innerHTML =
        "<p style='color: red;'>Error loading buildings.</p>";
    }
  }

  selectBuilding(buildingName: string | null) {
    (document.getElementById("building-results") as HTMLDivElement).style.display = "none";
    this.building = buildingName || null;
    const buildingInput = document.getElementById("building");
    if (buildingInput) (buildingInput as HTMLInputElement).value = buildingName || "";

    const resultsContainer = document.getElementById("building-results");
    if (resultsContainer) resultsContainer.innerHTML = "";

    this.updateQueryParams();
  }


  async getClassroom() {
    const classroomQuery = (document.getElementById("classroom") as HTMLInputElement).value;
    const resultsContainer = document.getElementById("classroom-results");
    (resultsContainer as HTMLDivElement).innerHTML = "";

    if (!classroomQuery) {
      return;
    }

    try {
      const response = await this.api.get(`/classroom?room=${classroomQuery}${this.building ? `&building=${this.building}`: ""}`);
      const data = response.data;

      if (!Array.isArray(data) || data.length === 0) {
        (resultsContainer as HTMLDivElement).innerHTML = "<p>No classrooms found.</p>";
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

        if (resultsContainer) {
          (resultsContainer as HTMLDivElement).style.display = "block";
          (resultsContainer as HTMLDivElement).appendChild(ul)
        };
    } catch (error) {
      (resultsContainer as HTMLDivElement).innerHTML =
        "<p style='color: red;'>Error loading classrooms.</p>";
    }
  }

  selectClassroom(classroomName: string | null) {
    (document.getElementById("classroom-results") as HTMLDivElement).style.display = "none";
    this.classroom = classroomName || null;
    const classroomInput = document.getElementById("classroom");
    if (classroomInput) (classroomInput as HTMLInputElement).value = classroomName || "";

    const resultsContainer = document.getElementById("classroom-results");
    if (resultsContainer) resultsContainer.innerHTML = "";

    this.updateQueryParams();
  }

  async changeGrid(view: string) {
    this.shouldFetchSchedule = false;
    this.grid = view;
    if (this.calendar) {
      await this.calendar.changeView(view);
      await this.updateCalendar();
    }
    this.shouldFetchSchedule = true;
  }

  updateQueryParams() {
    const queryParams = new URLSearchParams(window.location.search);

    if (this.number) {
      queryParams.set("number", this.number);
    } else {
      queryParams.delete("number");
    }

    if (this.building) {
      queryParams.set("building", this.building);
    } else {
      queryParams.delete("building");
    }

    if (this.classroom) {
      queryParams.set("room", this.classroom);
    } else {
      queryParams.delete("room");
    }

    if (this.subject) {
      queryParams.set("subject", this.subject);
    } else {
      queryParams.delete("subject");
    }

    if (this.teacher) {
      queryParams.set("teacher", this.teacher);
    } else {
      queryParams.delete("teacher");
    }

    if (this.startDate) {
      const formattedStartDate = this.startDate.toISOString().split('T')[0];
      queryParams.set("start", formattedStartDate);
    } else {
      queryParams.delete("start");
    }

    if (this.endDate) {
      const formattedEndDate = this.endDate.toISOString().split('T')[0];
      queryParams.set("end", formattedEndDate);
    } else {
      queryParams.delete("end");
    }

    const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
    history.pushState({}, "", newUrl);
  }

  async getSchedule() {
    const view = this.calendar.view;
    this.startDate = view.currentStart;
    this.endDate = view.currentEnd;
    const resultDiv = document.getElementById("result");
    (resultDiv as HTMLDivElement).style.display = "inline-block";
    (resultDiv as HTMLDivElement).innerHTML = "Loading...";

    try {
      this.number = (document.getElementById("studentNumber") as HTMLInputElement).value;
      this.updateQueryParams();

      const queryParams = new URLSearchParams(window.location.search);
      const response = await this.api.get(
        `/schedule?${queryParams.toString()}`
      );

      if (response.status !== 200) {
        console.error("Failed to fetch schedule");
      }

      const data = response.data;

      this.events = data.map((item: any) => ({
        title: item.title,
        start: item.start,
        end: item.end,
        description: item.description || "No description",
        workerTitle: item.workerTitle,
        worker: item.worker,
        room: item.room,
        groupName: item.groupName,
        tokName: item.tokName,
        lessonForm: item.lessonForm,
        lessonFormShort: item.lessonFormShort,
        lessonStatus: item.lessonStatus,
        color: item.color,
      }));

      console.log(this.events);

      if (!this.calendar) {
        await this.initializeCalendar();
      } else {
        await this.updateCalendar();
      }
      await this.createLegend();
      (resultDiv as HTMLDivElement).style.display = "none";
    } catch (error) {
      (resultDiv as HTMLDivElement).innerHTML = `<span style="color: red;">Error: ${error}</span>`;
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
      const eventInfoContainer = document.getElementById("event-info");
      this.calendar = new FullCalendar.Calendar(calendarElement, {
        initialView: this.grid,
        events: this.events,
        eventClick: function (info: any) {
          (eventInfoContainer as HTMLDivElement).innerHTML = "";
          const title = document.createElement("p");
          title.style.fontWeight = "bold";
          title.innerHTML = `${info.event.title}`;
          eventInfoContainer?.appendChild(title);

          const room = document.createElement("p");
          room.innerHTML = `<strong>Classroom:</strong> ${
            info.event.extendedProps.room || "No info"
          }`;
          eventInfoContainer?.appendChild(room);

          const time = document.createElement("p");
          time.innerHTML = `<strong>Time:</strong> ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}`;
          eventInfoContainer?.appendChild(time);

          const teacher = document.createElement("p");
          teacher.innerHTML = `<strong>Teacher:</strong> ${
            info.event.extendedProps.workerTitle || "No info"
          }`;
          eventInfoContainer?.appendChild(teacher);

          const description = document.createElement("p");
          description.innerHTML = `<strong>Description:</strong> ${
            info.event.extendedProps.description || "No info"
          }`;
          eventInfoContainer?.appendChild(description);
          if (eventInfoContainer) eventInfoContainer.style.display = "block";
          eventInfoContainer?.scrollIntoView({ behavior: "smooth" });
        },
        datesSet: (info: any) => {
          const newStartDate = info.start;
          const newEndDate = info.end;
  
          const isOutsideRange =
            !this.startDate ||
            !this.endDate ||
            newStartDate < this.startDate ||
            newEndDate > this.endDate;
  
          if (isOutsideRange) {
            this.startDate = newStartDate;
            this.endDate = newEndDate;
            this.updateQueryParams();
            if (this.shouldFetchSchedule && this.hasValidParams() !== null) {
              this.getSchedule();
            }
          }
        },
      });

      await this.calendar.render();
      const view = this.calendar.view;
      this.startDate = view.currentStart;
      this.endDate = view.currentEnd;
      this.updateQueryParams();
    } catch (error) {
      console.error(error);
    }
  }

  async createLegend() {
    const legendContainer = document.getElementById("legend");
    (legendContainer as HTMLDivElement).innerHTML = "";

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

      legendContainer?.appendChild(legendItem);
    });
  }

  hasValidParams(): string | null {
    const result =  this.number || this.classroom || this.subject || this.teacher;
    return result;
  }
}
