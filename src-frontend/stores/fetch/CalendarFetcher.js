import axios from "axios";

const BaseService = "/api/calendar";

export async function fetchProjectEvents(projectId, dateFrom, dateTo) {
    const resp = await axios.get(`${BaseService}/events/${projectId}/${dateFrom}/${dateTo}`);

    return resp.data;
}

const BaseEventService = BaseService + "/event";

export async function fetchCreateEvent(projectId, name, startDate, endDate, tags) {
    const resp = await axios.post(BaseEventService, {
        project: projectId,
        name: name,
        startDate: startDate,
        endDate: endDate,
        tags: tags
    });

    return resp.data;
}

export async function fetchUpdateEvent(eventId, event) {
    const resp = await axios.put(`${BaseEventService}/${eventId}`, {
        name: event.name,
        startDate: event.startDate,
        endDate: event.endDate,
    });

    return resp.data;
}

export async function fetchDeleteEvent(eventId) {
    const resp = await axios.delete(`${BaseEventService}/${eventId}`);

    return resp.data;
}

export async function fetchEventList(projectId, searchTerm) {
    const resp = await axios.get(`${BaseEventService}/list/${projectId}?search=${searchTerm}`);

    return resp.data;
}