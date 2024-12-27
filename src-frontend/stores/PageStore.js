import { defineStore } from 'pinia';
import { usePageTabStore  } from './PageTabStore';
import { ref } from 'vue';
import { fetchCreatePage, fetchDeletePage, fetchUpdatePage, fetchGetPage, fetchGetPageList } from '@/fetch/PageFetcher';

export const usePageStore = defineStore('page', () => {
    const pages = ref({});
    const selectedPage = ref(null);
    const pageTabStore = usePageTabStore();
    const displayedPages = ref([]);
    const isLoadingPage = ref(false); // used to prevent loading a page multiple times in different places, Page view gets blocked while this boolean is true

    function reorderDisplayedPages() {
        displayedPages.value = Object.values(displayedPages.value).sort((a, b) => {
            return a.name - b.name;
        });
    }
    
    /**
     * Cleanup method when the user navigates away from a page. This makes sure that all displayed data is cleared and the store is ready for the next page.
     */
    async function resetStore() {
        pages.value = {};
        selectedPage.value = null;
        isLoadingPage.value = false;
        pageTabStore.resetStore(); // this clears the tab store as well as the section store
    }

    async function setSelectedPage(page, forceRefresh = false) {
        resetStore(); // clean up the store before setting a new page
        selectedPage.value = page;

        // if the page has no loaded page tabs we assume that the serializer skipped the tabs.
        // thus, we force a reload of the page to get the tabs and additionally select the first tab as selected.
        if (forceRefresh || !page.pageTabs || page.pageTabs.length === 0) {
            selectedPage.value = null;
            isLoadingPage.value = true;

            getPage(page.id, true).then((fetchedPage) => {
                pages.value[fetchedPage.id] = fetchedPage;
                selectedPage.value = fetchedPage;

                if (fetchedPage.pageTabs.length > 0) {
                    pageTabStore.setSelectedTab(fetchedPage.pageTabs[0]);
                }

                isLoadingPage.value = false;
                addPage(fetchedPage);
            });
        } else {
            addPage(selectedPage.value); // to make sure all tabs are loaded into the store
        }
    }

    function getSelectedPage() {
        return selectedPage.value;
    }

    function createPage(page) {
        return new Promise((resolve) => {
            fetchCreatePage(page).then((newPage) => {
                addPage(newPage);
                resolve(newPage);
            });
        });
    }

    function addPage(page) {
        pages.value[page.id] = page;

        page.pageTabs.forEach((tab) => {
            pageTabStore.addTab(page.id, tab);
        });
    }

    function updatePage(page) {
        return new Promise((resolve) => {
            fetchUpdatePage(page).then((updatedPage) => {
                addPage(updatedPage);
                resolve(updatedPage);
            });
        });
    }

    function getPage(pageId, forceRefresh = false) {
        return new Promise((resolve) => {
            // the page is already loaded into the store.
            // we must check if the full object is available as the list endpoints only serialize a subset of the object to reduce the payload size.
            if (!forceRefresh && pages.value[pageId]?.pageTabs?.length > 0) {
                resolve(pages.value[pageId]);
            } else {
                fetchGetPage(pageId).then((page) => {
                    addPage(page);
                    resolve(page);
                });
            }
        });
    }

    function getPageList(projectId) {
        return new Promise((resolve) => {
            fetchGetPageList(projectId).then((pageList) => {
                pageList.forEach((page) => {
                    addPage(page);
                });

                resolve(pageList);
            });
        });
    }

    function deletePage(pageId) {
        return new Promise((resolve) => {
            fetchDeletePage(pageId).then(() => {
                if (selectedPage.value && selectedPage.value.id === pageId) {
                    selectedPage.value = null;
                }

                removePage(pageId);

                resolve();
            });
        });
    }

    function removePage(pageId) {
        // also filter it from the displayed pages
        if (displayedPages.value) {
            displayedPages.value = displayedPages.value.filter((p) => p.id !== pageId);
        }

        if (pages.value[pageId]) {
            delete pages.value[pageId];
        }
    }

    return {
        displayedPages,
        reorderDisplayedPages,
        pages,
        isLoadingPage,
        resetStore,
        selectedPage,
        setSelectedPage,
        getSelectedPage,
        createPage,
        addPage,
        updatePage,
        getPage,
        getPageList,
        deletePage,
        removePage,
    };
});