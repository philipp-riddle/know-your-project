import { defineStore } from 'pinia';
import { ref } from 'vue';
import { usePageStore } from '@/stores/PageStore';
import { useThreadStore } from '@/stores/ThreadStore';
import { fetchCreatePageSection, fetchUpdatePageSection, fetchDeletePageSection, fetchChangePageSectionOrder, fetchUploadPageSection } from '@/stores/fetch/PageFetcher';

export const usePageSectionStore = defineStore('pageSection', () => {
    // == helper variables
    const displayedPageSections = ref([]);
    const isDraggingPageSection = ref(false);
    const isCreatingPageSection = ref(false);
    const selectedPageSection = ref(null);

    // == helper stores
    const pageStore = usePageStore();
    const threadStore = useThreadStore();

    function resetStore () {
        displayedPageSections.value = [];
        isDraggingPageSection.value = false;
        selectedPageSection.value = null;
    }

    // == fetch + store methods

    async function createSection(pageTabId, pageSection) {
        return new Promise((resolve) => {
            isCreatingPageSection.value = true;

            fetchCreatePageSection(pageTabId, pageSection).then((newSection) => {
                if (newSection.pageTab.page.id === pageStore.selectedPage.id) {
                    displayedPageSections.value.push(newSection);
                }

                isCreatingPageSection.value = false;
                resolve(newSection);
            }).catch((error) => {
                isCreatingPageSection.value = false;
            });
        });
    }

    async function uploadSection(pageTabId, file) {
        const newSection = await fetchUploadPageSection(pageTabId, file);

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

        // if the deleted section had a thread and it was currently selected, deselect it as it is now deleted as well
        if (pageSection.threadContext?.thread.id === threadStore.selectedThread?.id) {
            threadStore.selectedThread = null;
        }

        return deletedSection;
    }

    async function reorderSections(pageTabId, sectionIds) {
        await fetchChangePageSectionOrder(pageTabId, sectionIds);
    }

    return {
        // helper variables
        isDraggingPageSection,
        isCreatingPageSection,
        displayedPageSections,
        selectedPageSection,

        // helper methods
        resetStore,

        // fetch + store methods
        createSection,
        uploadSection,
        updateSection,
        deleteSection,
        reorderSections,
    };
});