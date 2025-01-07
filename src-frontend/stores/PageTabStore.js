import { defineStore } from 'pinia';
import { ref } from 'vue';
import { usePageStore  } from './PageStore';
import { usePageSectionStore } from './PageSectionStore';
import { fetchCreatePageTab, fetchUpdatePageTab, fetchDeletePageTab } from '../fetch/PageFetcher';

export const usePageTabStore = defineStore('pageTab', () => {
    const pageTabs = ref({});
    const selectedTab = ref(null);
    const pageTabsByPage = ref({});
    const pageStore = usePageStore();
    const pageSectionStore = usePageSectionStore();

    function resetStore() {
        pageTabs.value = {};
        selectedTab.value = null;
        pageTabsByPage.value = {};
        pageSectionStore.resetStore();
    }

    async function setSelectedTab(pageTab) {
        selectedTab.value = pageTab;

        // first, write the displayed page sections to the store
        pageSectionStore.displayedPageSections = pageTab.pageSections;
        // then order the displayed page sections by their order index
        pageSectionStore.displayedPageSections = pageSectionStore.displayedPageSections.sort((a, b) => a.orderIndex - b.orderIndex);
        
        // if the user switches to a tab with no sections we automatically add a text field for the user to start with
        if (pageSectionStore.displayedPageSections.length === 0) {
            pageSectionStore.displayedPageSections.push({
                pageSectionText: {
                    content: '',
                },
            });
        }
    }

    async function getTab(pageTabId) {
        const tab = pageTabs.value[pageTabId] ?? null;

        if (!tab) {
            console.error('Cannot get tab with id:', pageTabId);

            return null;
        }

        return tab;
    }
    
    async function createTab(pageId, pageTab) {
        const newTab = await fetchCreatePageTab(pageId, pageTab);

        if (!newTab) {
            return;
        }

        const addedTab =  addTab(pageId, newTab);
        pageStore.displayedPages[pageId].pageTabs.push(pageTab);

        return addedTab;
    }

    async function updateTab(pageTab) {
        const updatedSection = await fetchUpdatePageTab(pageTab);

        if (!updatedSection) {
            console.error('Failed to update section with id:', pageTab.id);
        }

        return updatedSection;
    }

    async function addTab(pageId, pageTab) {
        const page = pageStore.displayedPages[pageId] ?? null;

        if (!page) {
            console.error('Cannot add tab to non-existent page with id:', pageId);
            return;
        }

        if (pageTabs.value[pageTab.id]) {
            return;
        }

        pageTabs.value[pageTab.id] = pageTab;

        if (!pageTabsByPage.value[pageId]) {
            pageTabsByPage.value[pageId] = [];
        }

        pageTabsByPage.value[pageId].push(pageTab);

        return pageTab;
    }

    async function deleteTab(pageId, pageTab) {
        const deleteTab = await fetchDeletePageTab(pageTab.id);

        if (!deleteTab) {
            return;
        }

        delete pageTabs.value[pageTab.id];
        const index = pageTabsByPage.value[pageId].findIndex((t) => t.id === pageTab.id);

        if (index === -1) {
            return;
        }

        pageTabsByPage.value[pageId].splice(index, 1);
        
        return deleteTab;
    }

    return {
        resetStore,
        pageTabs,
        selectedTab,
        setSelectedTab,
        pageTabsByPage,
        getTab,
        createTab,
        updateTab,
        addTab,
        deleteTab,
    };
});