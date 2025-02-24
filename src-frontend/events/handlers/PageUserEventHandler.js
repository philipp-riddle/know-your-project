import { usePageStore } from '@/stores/PageStore';

/**
 * Handles all Mercure events and actions (create, delete) related to the PageUser entity.
 */
export function usePageUserEventHandler() {
    const pageStore = usePageStore();

    const handle = (event) => {
        const pageUser = event.entity;
        const page = pageUser.page;

        if (!pageStore.displayedPages[page.id]) {
            console.error('not included in displayed pages...');
            return; // if page is not displayed we do not need to update the users
        }

        if (event.action == 'create') {
            pageStore.displayedPages[page.id].users.push(pageUser);
        } else if (event.action == 'delete') {
            pageStore.displayedPages[page.id].users = pageStore.displayedPages[page.id].users.filter((up) => up.id !== pageUser.id);
        } else {
            console.error('Unknown page user action', event.action);
            console.error('Event', event);
        }
    };

    return {
        handle,
    };
};