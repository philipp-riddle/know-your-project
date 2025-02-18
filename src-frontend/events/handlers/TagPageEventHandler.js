import { usePageStore } from '@/stores/PageStore';
import { useTagStore } from '@/stores/TagStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the TagPage entity.
 */
export function useTagPageEventHandler() {
    const pageStore = usePageStore();
    const tagStore = useTagStore();

    const handle = (event) => {
        const isForSelectedPage = pageStore.selectedPage && event.entity.page.id === pageStore.selectedPage.id;

        if (event.action == 'create') {
            if (isForSelectedPage) {
                pageStore.selectedPage.tags.push(event.entity);
            }

            tagStore.addTag(event.entity.tag, event.entity.tag.parent);
            tagStore.addPageToTags(event.entity.page, [event.entity.tag.id]);
        } else if (event.action == 'delete') {
            if (isForSelectedPage) {
                pageStore.selectedPage.tags = pageStore.selectedPage.tags.filter((tp) => tp.tag.id !== event.entity.tag.id);
            }

            tagStore.removeTagFromPage(event.entity.page, event.entity.tag);
        } else {
            console.error('Unknown tag page action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};