import { useTagStore } from '@/stores/TagStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the Tag entity.
 */
export function useTagEventHandler() {
    const tagStore = useTagStore();

    const handle = (event) => {
        if (event.action == 'create') {
            tagStore.addTag(event.entity, event.entity.parent);
        } else if (event.action == 'update') {
            tagStore.addTag(event.entity, event.entity.parent);
        } else if (event.action == 'delete') {
            tagStore.removeTag(event.entity);
        } else {
            console.error('Unknown tag action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};