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

    async function setSelectedTab(pageTab) {
        selectedTab.value = pageTab;

        pageSectionStore.displayedPageSections = pageTab.pageSections;
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
        pageStore.pages[pageId].pageTabs.push(pageTab);

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
        const page = pageStore.pages[pageId] ?? null;

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