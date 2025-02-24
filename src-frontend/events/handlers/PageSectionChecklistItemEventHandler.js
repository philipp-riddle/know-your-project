import { usePageStore } from '@/stores/PageStore';
import { usePageSectionStore } from '@/stores/PageSectionStore';
import { usePageSectionChecklistItemStore } from '@/stores/PageSectionChecklistItemStore';

/**
 * Handles all Mercure events and actions (create, update, delete) related to the PageSectionChecklistItem entity.
 */
export function usePageSectionChecklistItemEventHandler() {
    const pageStore = usePageStore();
    const pageSectionStore = usePageSectionStore();
    const pageSectionChecklistItemStore = usePageSectionChecklistItemStore();

    const handle = (event) => {
        let pageSection = event.entity.pageSectionChecklist.pageSection;
        let checklist = event.entity.pageSectionChecklist;
        checklist.pageSectionChecklistItems = checklist.pageSectionChecklistItems.map((item) => {
            // if the items is numeric, we need to replace it with the actual item itself
            if (!isNaN(parseInt(item))) {
                return event.entity; // replace the id with the actual item; this is due to circular references
            }

            return item;
        });

        pageSection.pageSectionChecklist = checklist; // set the enriched checklist back to the page section
        const page = pageSection.pageTab.page;
        const isSelectedPage = page.id === pageStore.selectedPage?.id;

        if (!isSelectedPage && !event.entity.task) {
            return; // if is not about the selected page and no task, it is not relevant.
        }

        const pageSectionIndex = pageSectionStore.displayedPageSections.findIndex((ps) => ps.id === pageSection.id);

        if (event.action == 'create' && isSelectedPage) {
            pageSectionChecklistItemStore.addChecklistItem(pageSection.id, event.entity);
        } else if (event.action == 'update' && isSelectedPage) {
            pageSectionStore.displayedPageSections[pageSectionIndex] = pageSection; // @todo this could get a bit problematic if two users edit the same piece at the same time
        } else if (event.action == 'delete' && isSelectedPage) {
            pageSectionChecklistItemStore.removeChecklistItem(pageSection.id, event.entity);
        } else if (!event.entity.task) { // if the event does not match any of the above and is not connected to a task, we need to abort.
            console.error('Unknown page section action', event.action);
            console.error('Event', event);
            return;
        }

        // after the updates are done push the update to the displayed pages
        if (pageStore.displayedPages[page.id]) {
            const pageStoreSectionIndex = pageStore.displayedPages[page.id].pageSections.findIndex((ps) => ps.id === pageSection.id);

            if (pageStoreSectionIndex !== -1) {
                pageStore.displayedPages[page.id].pageSections[pageStoreSectionIndex] = pageSection;
            } else {
                console.error('Page section not found in displayed page', pageSection.id);
            }
        } else {
            console.error('Page not found in displayed pages', page.id);
        }
    };

    return {
        handle,
    };
};