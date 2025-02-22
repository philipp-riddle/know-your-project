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
            tagStore.tags = tagStore.tags.map((tag) => {
                if (tag.id == event.entity.id) {
                    return event.entity;
                }

                return tag;
            });
        } else if (event.action == 'delete') {
            tagStore.removeTag(event.entity);
        } else if (event.action == 'order') {
            const newTagEntities = event.entities;

            // replace the tag entities in the store with the new ones
            tagStore.tags = tagStore.tags.map((tag) => {
                return newTagEntities.find((newTag) => newTag.id == tag.id) ?? tag;
            });
            tagStore.tags.sort((a, b) => a.orderIndex - b.orderIndex); // sort the tags by their order index

            // also, regenerate all the nested IDs map for the parent tag
            const parent = event.entities[0].parent;
            
            if (parent) { // if there is no parent it means that the root tags were reordered; we catch this edge case here and ignore it.
                let newNestedTagIdMap = {};

                for (const reorderedTag of newTagEntities) {
                    newNestedTagIdMap[reorderedTag.orderIndex] = reorderedTag.id;
                }

                tagStore.nestedTagIdMap[parent.id] = newNestedTagIdMap;
            }
        } else {
            console.error('Unknown tag action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};