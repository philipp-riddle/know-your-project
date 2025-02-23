import { useCalendarStore  } from '@/stores/CalendarStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the CalendarEvent entity.
 */
export function useCalendarEventHandler() {
    const calendarStore = useCalendarStore();

    const handle = (event) => {
        if (event.action == 'create') {
            calendarStore.events['events'].push(event.entity);
            calendarStore.generateEventHashmap();
        } else if (event.action == 'update') {
            calendarStore.events['events'] = calendarStore.events['events'].map((ev) => {
                if (ev.id === event.entity.id) {
                    return event.entity;
                }

                return ev;
            });
            calendarStore.generateEventHashmap();
        } else if (event.action == 'delete') {
            calendarStore.events['events'] = calendarStore.events['events'].filter((ev) => ev.id !== event.entity.id);
            calendarStore.generateEventHashmap();
        } else {
            console.error('Unknown calendar event action', event.action);
            console.error('Event', event);
            return;
        }

        
    };

    return {
        handle,
    };
};