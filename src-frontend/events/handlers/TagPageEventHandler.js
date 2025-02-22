import { usePageStore } from '@/stores/PageStore';
import { useTagStore } from '@/stores/TagStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the TagPage entity.
 */
export function useTagPageEventHandler() {
    const pageStore = usePageStore();
    const tagStore = useTagStore();

    const handle = (event) => {
        const isForSelectedPage = event.entity && pageStore.selectedPage && event.entity.page.id === pageStore.selectedPage.id;

        if (event.action == 'create') {
            if (isForSelectedPage) {
                pageStore.selectedPage.tags.push(event.entity);
            }

            tagStore.addTag(event.entity.tag, event.entity.tag.parent);
            tagStore.addPageToTags(event.entity.page, [event.entity.id]);
        } else if (event.action == 'delete') {
            if (isForSelectedPage) {
                pageStore.selectedPage.tags = pageStore.selectedPage.tags.filter((tp) => tp.tag.id !== event.entity.tag.id);
            }

            tagStore.removeTagFromPage(event.entity.page, event.entity.tag);
        } else if (event.action == 'order') { // reordering tag pages means reordering all pages in the associated tag
            // set new tag pages in the store!
            let newTagPages = {};

            for (const tagPage of event.entities) {
                newTagPages[tagPage.orderIndex] = tagPage;
            }

            tagStore.tagPages[event.entities[0].tag.id] = newTagPages;
        } else {
            console.error('Unknown tag page action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};