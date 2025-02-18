import { usePageStore } from '@/stores/PageStore';
import { useTaskStore } from '@/stores/TaskStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the Task entity.
 */
export function useTaskEventHandler() {
    const pageStore = usePageStore();
    const taskStore = useTaskStore();

    const handle = (event) => {
        if (event.action == 'create') {
            taskStore.addTask(event.entity);
        } else if (event.action == 'update') {
            taskStore.addTask(event.entity);

            if (pageStore.selectedPage?.id == event.entity.page.id) {
                pageStore.selectedPage.task = event.entity;
            }
        } else if (event.action == 'delete') {
            taskStore.removeTaskFromStore(event.entity);
        } else {
            console.error('Unknown task action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};