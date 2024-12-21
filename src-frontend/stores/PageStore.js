import { defineStore } from 'pinia';
import { usePageTabStore  } from './PageTabStore';
import { ref } from 'vue';
import { fetchCreatePage, fetchDeletePage, fetchUpdatePage, fetchGetPage, fetchGetPageList } from '@/fetch/PageFetcher';

export const usePageStore = defineStore('page', () => {
    const pages = ref({});
    const selectedPage = ref(null);
    const pageTabStore = usePageTabStore();
    const displayedPages = ref([]);


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
        pageTabStore.resetStore(); // this clears the tab store as well as the section store
    }

    async function setSelectedPage(page) {
        selectedPage.value = page;

        // @todo clean up old data from old selected page in the stores
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

    function getPage(pageId) {
        return new Promise((resolve) => {
            if (pages.value[pageId]) {
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

                // also filter it from the displayed pages
                if (displayedPages.value) {
                    displayedPages.value = displayedPages.value.filter((p) => p.id !== pageId);
                }

                delete pages.value[pageId];

                resolve();
            });
        });
    }

    return {
        pages,
        selectedPage,
        displayedPages,
        reorderDisplayedPages,
        resetStore,
        setSelectedPage,
        getSelectedPage,
        createPage,
        addPage,
        updatePage,
        getPage,
        getPageList,
        deletePage
    };
});