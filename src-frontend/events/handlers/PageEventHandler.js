import { usePageStore } from '@/stores/PageStore';
import { useTaskStore } from '@/stores/TaskStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the Page entity.
 */
export function usePageEventHandler() {
    const pageStore = usePageStore();
    const taskStore = useTaskStore();

    const handle = (event) => {
        if (event.action == 'create') {
            pageStore.addPage(event.entity);
        } else if (event.action == 'update') {
            pageStore.addPage(event.entity);

            if (pageStore.selectedPage?.id == event.entity.id) {
                pageStore.setSelectedPage(event.entity);
            }

            let task = event.entity.task;

            // if the page is associated with a task we need to update the task as well
            if (task && taskStore.tasks[task.stepType]) {
                task.page = event.entity;
                taskStore.tasks[task.stepType] = taskStore.tasks[task.stepType].map((t) => {
                    if (t.id == task.id) {
                        return task;
                    }

                    return t;
                });
            }
        } else if (event.action == 'delete') {
            pageStore.removePage(event.entity);
        } else {
            console.error('Unknown page action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};