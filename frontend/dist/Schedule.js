"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
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
        // this.parseUrlParams();
        this.setupEventListeners();
        if (this.hasValidParams()) {
            this.getSchedule();
        }
        // window.addEventListener("popstate", (event) => {
        //   this.parseUrlParams();
        //   if (this.hasValidParams()) {
        //     this.getSchedule();
        //   }
        // });
    }
    setupEventListeners() {
        var _a, _b, _c, _d, _e, _f, _g;
        (_a = document.getElementById("scheduleForm")) === null || _a === void 0 ? void 0 : _a.addEventListener("submit", (e) => {
            e.preventDefault();
            this.getSchedule();
        });
        (_b = document.getElementById("dayViewButton")) === null || _b === void 0 ? void 0 : _b.addEventListener("click", () => this.changeGrid("timeGridDay"));
        (_c = document.getElementById("weekViewButton")) === null || _c === void 0 ? void 0 : _c.addEventListener("click", () => this.changeGrid("dayGridWeek"));
        (_d = document.getElementById("monthViewButton")) === null || _d === void 0 ? void 0 : _d.addEventListener("click", () => this.changeGrid("dayGridMonth"));
        (_e = document.getElementById("classroom")) === null || _e === void 0 ? void 0 : _e.addEventListener("change", this.getClassroom.bind(this));
        (_f = document.getElementById("subject")) === null || _f === void 0 ? void 0 : _f.addEventListener("change", this.getSubject.bind(this));
        (_g = document.getElementById("teacher")) === null || _g === void 0 ? void 0 : _g.addEventListener("change", this.getTeacher.bind(this));
    }
    parseUrlParams() {
        const params = new URLSearchParams(window.location.search);
        this.number = params.get("number") || null;
        this.classroom = params.get("room") || null;
        this.subject = params.get("subject") || null;
        this.teacher = params.get("teacher") || null;
        if (this.number)
            document.getElementById("studentNumber").value = this.number;
        if (this.classroom)
            document.getElementById("classroom").value = this.classroom;
        if (this.subject)
            document.getElementById("subject").value = this.subject;
        if (this.teacher)
            document.getElementById("teacher").value = this.teacher;
    }
    getTeacher() {
        return __awaiter(this, void 0, void 0, function* () {
            const teacherQuery = document.getElementById("teacher").value;
            const resultsContainer = document.getElementById("teacher-results");
            if (resultsContainer)
                resultsContainer.innerHTML = "";
            if (!teacherQuery) {
                return;
            }
            try {
                const response = yield this.api.get(`/teacher?teacher=${teacherQuery}`);
                const data = response.data;
                if ((!Array.isArray(data) || data.length === 0) && resultsContainer) {
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
                if (resultsContainer)
                    resultsContainer.appendChild(ul);
            }
            catch (error) {
                if (resultsContainer)
                    resultsContainer.innerHTML = "<p style='color: red;'>Error loading teachers.</p>";
            }
        });
    }
    selectTeacher(teacherName) {
        this.teacher = teacherName;
        document.getElementById("teacher").value = teacherName;
        document.getElementById("teacher-results").innerHTML = "";
        this.updateQueryParams();
    }
    getSubject() {
        return __awaiter(this, void 0, void 0, function* () {
            const subjectQuery = document.getElementById("subject").value;
            const resultsContainer = document.getElementById("subject-results");
            resultsContainer.innerHTML = "";
            if (!subjectQuery) {
                return;
            }
            try {
                const response = yield this.api.get(`/subject?subject=${subjectQuery}`);
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
            }
            catch (error) {
                resultsContainer.innerHTML =
                    "<p style='color: red;'>Error loading subjects.</p>";
            }
        });
    }
    selectSubject(subjectName) {
        this.subject = subjectName;
        document.getElementById("subject").value = subjectName;
        document.getElementById("subject-results").innerHTML = "";
        this.updateQueryParams();
    }
    getClassroom() {
        return __awaiter(this, void 0, void 0, function* () {
            const classroomQuery = document.getElementById("classroom").value;
            const resultsContainer = document.getElementById("classroom-results");
            resultsContainer.innerHTML = "";
            if (!classroomQuery) {
                return;
            }
            try {
                const response = yield this.api.get(`/classroom?room=${classroomQuery}`);
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
                    li.addEventListener("click", () => this.selectClassroom(classroom.item));
                    ul.appendChild(li);
                });
                resultsContainer.appendChild(ul);
            }
            catch (error) {
                resultsContainer.innerHTML =
                    "<p style='color: red;'>Error loading classrooms.</p>";
            }
        });
    }
    selectClassroom(classroomName) {
        this.classroom = classroomName;
        document.getElementById("classroom").value = classroomName;
        document.getElementById("classroom-results").innerHTML = "";
        this.updateQueryParams();
    }
    changeGrid(view) {
        return __awaiter(this, void 0, void 0, function* () {
            this.grid = view;
            if (this.calendar) {
                yield this.calendar.changeView(view);
            }
        });
    }
    updateQueryParams() {
        const queryParams = new URLSearchParams();
        if (this.number)
            queryParams.set("number", this.number);
        if (this.classroom)
            queryParams.set("room", this.classroom);
        if (this.subject)
            queryParams.set("subject", this.subject);
        if (this.teacher)
            queryParams.set("teacher", this.teacher);
        const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
        history.pushState({}, "", newUrl);
    }
    getSchedule() {
        return __awaiter(this, void 0, void 0, function* () {
            const resultDiv = document.getElementById("result");
            resultDiv.innerHTML = "Loading...";
            try {
                this.number = document.getElementById("studentNumber").value;
                this.updateQueryParams();
                const queryParams = new URLSearchParams(window.location.search);
                const response = yield this.api.get(`/schedule?${queryParams.toString()}`);
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
                    yield this.initializeCalendar();
                }
                else {
                    this.updateCalendar();
                }
                this.createLegend();
            }
            catch (error) {
                resultDiv.innerHTML = `<span style="color: red;">Error: ${error}</span>`;
            }
        });
    }
    updateCalendar() {
        return __awaiter(this, void 0, void 0, function* () {
            try {
                this.calendar.removeAllEvents();
                this.calendar.addEventSource(this.events);
                this.calendar.render();
            }
            catch (error) {
                console.error(error);
            }
        });
    }
    initializeCalendar() {
        return __awaiter(this, void 0, void 0, function* () {
            try {
                const calendarElement = document.getElementById("calendar");
                const eventInfoContainer = document.getElementById("event-info");
                this.calendar = new Calendar.Calendar(calendarElement, {
                    initialView: this.grid,
                    events: this.events,
                    eventClick: function (info) {
                        eventInfoContainer.innerHTML = "";
                        const title = document.createElement("p");
                        title.style.fontWeight = "bold";
                        title.innerHTML = `${info.event.title}`;
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.appendChild(title);
                        const room = document.createElement("p");
                        room.innerHTML = `<strong>Classroom:</strong> ${info.event.extendedProps.room || "No info"}`;
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.appendChild(room);
                        const time = document.createElement("p");
                        time.innerHTML = `<strong>Time:</strong> ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}`;
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.appendChild(time);
                        const teacher = document.createElement("p");
                        teacher.innerHTML = `<strong>Teacher:</strong> ${info.event.extendedProps.workerTitle || "No info"}`;
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.appendChild(teacher);
                        const description = document.createElement("p");
                        description.innerHTML = `<strong>Description:</strong> ${info.event.extendedProps.description || "No info"}`;
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.appendChild(description);
                        if (eventInfoContainer)
                            eventInfoContainer.style.display = "block";
                        eventInfoContainer === null || eventInfoContainer === void 0 ? void 0 : eventInfoContainer.scrollIntoView({ behavior: "smooth" });
                    },
                });
                yield this.calendar.render();
            }
            catch (error) {
                console.error(error);
            }
        });
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
            legendContainer === null || legendContainer === void 0 ? void 0 : legendContainer.appendChild(legendItem);
        });
    }
    hasValidParams() {
        return this.number || this.classroom || this.subject || this.teacher;
    }
}
