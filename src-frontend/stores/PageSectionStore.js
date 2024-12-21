import { defineStore } from 'pinia';
import { ref } from 'vue';
import { usePageTabStore } from './PageTabStore';
import { fetchCreatePageSection, fetchUpdatePageSection, fetchDeletePageSection, fetchChangePageSectionOrder } from '../fetch/PageFetcher';

export const usePageSectionStore = defineStore('pageSection', () => {
    const pageSections = ref({});

    /**
     * The following two state variables are used to keep track of the section that the user is currently adding to the page.
     */
    const pageSectionAddIndex = ref(null);
    const pageSectionAddType = ref(null);

    const displayedPageSections = ref([]);
    const pageSectionsByTab = ref({});
    const pageTabStore = usePageTabStore();
    const isDraggingPageSection = ref(null);

    function resetStore () {
        pageSections.value = {};
        pageSectionAddIndex.value = null;
        pageSectionAddType.value = null;
        displayedPageSections.value = [];
        pageSectionsByTab.value = {};
    }

    // == fetch + store methods

    async function createSection(pageTabId, pageSection) {
        const newSection = await fetchCreatePageSection(pageTabId, pageSection);

        if (!newSection) {
            return;
        }

        displayedPageSections.value.push(newSection);

        return newSection;
    }

    async function updateSection(pageSection) {
        const pageSectionId = pageSection.id;
        delete pageSection.id;
        const updatedSection = await fetchUpdatePageSection(pageSectionId, pageSection);

        if (!updatedSection) {
            console.error('Failed to update section with id:', pageSection.id);
        }

        displayedPageSections.value = displayedPageSections.value.map((s) => { return s.id === pageSectionId ? updatedSection : s; });

        return updatedSection;
    }

    async function deleteSection(pageSection) {
        const deletedSection = await fetchDeletePageSection(pageSection.id);

        if (!deletedSection) {
            console.error('Failed to delete section with id:', pageSection.id);
        }

        displayedPageSections.value = displayedPageSections.value.filter((s) => s.id !== pageSection.id);

        return deletedSection;
    }

    async function reorderSections(pageTabId, sectionIds) {
        const tab = await pageTabStore.getTab(pageTabId);

        if (!tab) {
            console.error('Cannot reorder sections for non-existent tab with id:', pageTabId);
            return;
        }

        await fetchChangePageSectionOrder(pageTabId, sectionIds);
    }

    // == store-only methods

    async function addSection(pageTabId, pageSection) {
        const tab = await pageTabStore.getTab(pageTabId);

        if (!tab) {
            console.error('Cannot add section to non-existent tab with id:', pageTabId);
            return;
        }

        if (pageSections.value[pageSection.id]) {
            return;
        }

        // pageTabStore.value[pageTabId].pageSections.push(pageSection);
        pageSections.value[pageSection.id] = pageSection;

        if (!pageSectionsByTab.value[pageTabId]) {
            pageSectionsByTab.value[pageTabId] = [];
        }

        pageSectionsByTab.value[pageTabId].push(pageSection);

        return pageSection;
    }

    async function removeSection(pageTabId, pageSection) {
        const tab = await pageTabStore.getTab(pageTabId);

        if (!tab) {
            console.error('Cannot remove section from non-existent tab with id:', pageTabId);
            return;
        }

        const index = pageSectionsByTab.value[pageTabId].findIndex((s) => s.id === pageSection.id);

        if (index === -1) {
            console.error('Cannot remove non-existent section with id:', pageSection.id);
            return;
        }

        pageSectionsByTab.value[pageTabId].splice(index, 1);
        delete pageSections.value[pageSection.id];

        return pageSection;
    }

    return {
        resetStore,

        pageSectionAddIndex,
        pageSectionAddType,

        isDraggingPageSection,

        pageSections,
        pageSectionsByTab,
        displayedPageSections,

        createSection,
        updateSection,
        deleteSection,
        reorderSections,
        
        addSection,
        removeSection,
    };
});