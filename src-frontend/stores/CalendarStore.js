import { defineStore } from 'pinia';
import { watch, ref } from 'vue';
import { useDateConverter } from '@/composables/DateConverter';
import { fetchProjectEvents, fetchCreateEvent, fetchUpdateEvent, fetchDeleteEvent } from '@/stores/fetch/CalendarFetcher';
import { useProjectStore } from '@/stores/ProjectStore';

export const useCalendarStore = defineStore('calendar', () => {
    const dateConverter = useDateConverter();
    const projectStore = useProjectStore();
    const events = ref([]);
    const eventHashmap = ref({});

    /**
     * Watch for event updates;
     * When the events are updated we generate a new event hashmap.
     */
    watch (() => events.value, (newEvents) => {
        generateEventHashmap(newEvents);
    }, { deep: true });

    const getEvents = async (startDate, endDate) => {
        // before passing the start & end day to the API we convert the range to an UTC date range; this way it is timezone agnostic.
        const startDateUTC = dateConverter.convertLocalDateToISOString(startDate);
        const endDateUTC = dateConverter.convertLocalDateToISOString(endDate);
        events.value = await fetchProjectEvents(projectStore.selectedProject.id, startDateUTC, endDateUTC);

        return events.value;
    };

    /**
     * This event hashmap helps us in the Calendar component to quickly find the events for a specific day.
     * It groups all available events by their start date and event type (tasks vs events).
     * Events by a certain date can be retrieved in O(1) time complexity as opposed to O(n) when iterating over all events.
     */
    const getEventHashmap = async (startDate, endDate) => {
        const newEvents = await getEvents(startDate, endDate);
        
        generateEventHashmap(newEvents);
    }

    const generateEventHashmap = (eventsForHashmap) => {
        eventsForHashmap = eventsForHashmap || events.value;
        eventHashmap.value = {};

        for (const eventType of Object.keys(eventsForHashmap)) {
            for (const event of eventsForHashmap[eventType]) {
                let eventDate = null;

                if (eventType === 'tasks') {
                    // convert the given due date to a UTC date; newDate() triggers the conversion to the correct local timezone.
                    eventDate = event.dueDate;
                } else if (eventType === 'events') {
                    eventDate = event.startDate;
                }

                if (!eventDate) {
                    console.error('Event date not found', event);
                    return;
                }

                // convert the UTC event date to the local time zone of the user and extract the date part.
                const eventFormattedDate = dateConverter.convertUTCToLocalDateString(eventDate).split('T')[0]; // e.g. '2025-01-01'

                if (!eventHashmap.value[eventFormattedDate]) {
                    eventHashmap.value[eventFormattedDate] = {
                        tasks: [],
                        events: [],
                    };
                }

                eventHashmap.value[eventFormattedDate][eventType].push(event);
            }
        }

        return eventHashmap.value;
    };

    const createEvent = (name, startDate, endDate, tags) => {
        const startDateUTC = dateConverter.convertLocalDateToISOString(startDate);
        const endDateUTC = endDate ? dateConverter.convertLocalDateToISOString(endDate) : null;

        return fetchCreateEvent(projectStore.selectedProject.id, name, startDateUTC, endDateUTC, tags).then((createdEvent) => {
            events.value['events'].push(createdEvent);
            generateEventHashmap(events.value); // generate a new event hashmap with the new event inside
        });
    };
    
    const updateEvent = async (event) => {
        return fetchUpdateEvent(event.id, event).then((updatedEvent) => {
            events.value['events'] = events.value['events'].map((event) => {
                if (event.id === updatedEvent.id) {
                    return updatedEvent;
                }

                return event;
            });

            generateEventHashmap(events.value); // generate a new event hashmap with the updated event inside
        });
    }

    const deleteEvent = async (event) => {
        return fetchDeleteEvent(event.id).then(() => {
            events.value['events'] = events.value['events'].filter((ev) => ev.id !== event.id);
            generateEventHashmap(events.value); // generate a new event hashmap without the deleted event
        });
    };

    return {
        events,
        eventHashmap,
        getEvents,
        getEventHashmap,
        generateEventHashmap,
        createEvent,
        updateEvent,
        deleteEvent,
    }
});