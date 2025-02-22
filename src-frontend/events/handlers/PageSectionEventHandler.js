import { usePageStore } from '@/stores/PageStore';
import { usePageSectionStore } from '@/stores/PageSectionStore';

/**
 * Handles all Mercure events and actions (create, update, order, delete) related to the PageSection entity.
 */
export function usePageSectionEventHandler() {
    const pageStore = usePageStore();
    const pageSectionStore = usePageSectionStore();

    const handle = (event) => {
        const page = event.entity?.pageTab.page ?? event.entities[0]?.pageTab.page;
        const isSelectedPage = page.id === pageStore.selectedPage?.id;

        if (!page || (!isSelectedPage && !event.entity.task && event.action !== 'order')) {
            return; // if is not about the selected page, no task and no order event it is not relevant.
        }

        if (event.action == 'create' && isSelectedPage) {
            pageSectionStore.displayedPageSections.push(event.entity);
        } else if (event.action == 'update') {
            if (isSelectedPage) {
                pageSectionStore.displayedPageSections = pageSectionStore.displayedPageSections.map((ps) => {
                    if (ps.id == event.entity.id) {
                        return event.entity;
                    }

                    return ps;
                });
            }

            if (pageStore.pages[page.id]) {
                pageStore.pages[page.id].pageSections = pageStore.pages[page.id].pageSections.map((ps) => {
                    if (ps.id == event.entity.id) {
                        return event.entity;
                    }
    
                    return ps;
                });
            }
        } else if (event.action == 'order' && isSelectedPage) {
            pageSectionStore.displayedPageSections = event.entities;
        } else if (event.action == 'delete') {
            if (isSelectedPage) {
                pageSectionStore.displayedPageSections = pageSectionStore.displayedPageSections.filter((ps) => ps.id !== event.entity.id);
            }

            if (pageStore.pages[page.id]) {
                pageStore.pages[page.id].pageSections = pageStore.pages[page.id].pageSections.filter((ps) => ps.id !== event.entity.id);
            }
        } else {
            console.error('Unknown page section action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};