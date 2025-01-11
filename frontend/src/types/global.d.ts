declare const axios: typeof import("axios");
declare const FullCalendar: typeof import('@fullcalendar/core');

interface Event {
    title: string;
    start: string;
    end: string;
    description: string;
    workerTitle: string;
    worker: string;
    room: string;
    groupName: string;
    tokName: string;
    lessonForm: string;
    lessonFormShort: string;
    lesson_status: string;
    color: string;
  }