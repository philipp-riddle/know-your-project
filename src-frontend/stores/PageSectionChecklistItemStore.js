import { defineStore } from 'pinia';
import { usePageSectionStore } from './PageSectionStore';
import { fetchCreatePageSectionChecklistItem, fetchUpdatePageSectionChecklistItem, fetchDeletePageSectionChecklistItem } from '@/stores/fetch/PageFetcher';

export const usePageSectionChecklistItemStore = defineStore('pageSectionChecklistItem', () => {
    const pageSectionStore = usePageSectionStore();

    async function createChecklistItem(pageSectionId, checklistItem) {
        const newChecklistItem = await fetchCreatePageSectionChecklistItem(checklistItem);

        if (!newChecklistItem) {
            return;
        }

        const addedChecklistItem = addChecklistItem(pageSectionId, newChecklistItem);

        return addedChecklistItem;
    }

    async function updateChecklistItem(pageSectionId, checklistItem) {
        const updatedChecklistItem = await fetchUpdatePageSectionChecklistItem(checklistItem);

        if (!updatedChecklistItem) {
            console.error('Failed to update checklist item with id:', checklistItem.id);
        }

        // pageSectionStore.pageSections[pageSectionId].pageSectionChecklist.pageSectionChecklistItems = pageSectionStore.pageSections[pageSectionId].pageSectionChecklist.pageSectionChecklistItems.map(item => item.id === checklistItem.id ? checklistItem : item);

        return updatedChecklistItem;
    }

    async function deleteChecklistItem(pageSectionId, checklistItem) {
        const deletedChecklistItem = await fetchDeletePageSectionChecklistItem(checklistItem);

        if (!deletedChecklistItem) {
            console.error('Failed to delete checklist item with id:', checklistItem.id);
        }

        removeChecklistItem(pageSectionId, checklistItem);
    }

    function addChecklistItem(pageSectionId, checklistItem) {
        const sectionIndex = pageSectionStore.displayedPageSections.findIndex(section => section.id === pageSectionId);

        if (sectionIndex === -1) {
            console.error('Cannot add checklist item to non-existent section with id:', pageSectionId);
            return;
        }

        pageSectionStore.displayedPageSections[sectionIndex].pageSectionChecklist.pageSectionChecklistItems.push(checklistItem);

        return checklistItem;
    }

    function removeChecklistItem(pageSectionId, checklistItem) {
        const sectionIndex = pageSectionStore.displayedPageSections.findIndex(section => section.id === pageSectionId);

        if (sectionIndex === -1) {
            console.error('Cannot delete checklist item from non-existent section with id:', pageSectionId);
            return;
        }

        const section = pageSectionStore.displayedPageSections[sectionIndex];
        const newChecklistItems = section.pageSectionChecklist.pageSectionChecklistItems.filter(item => item.id !== checklistItem.id);
        pageSectionStore.displayedPageSections[sectionIndex].pageSectionChecklist.pageSectionChecklistItems = newChecklistItems;

        return checklistItem;
    }

    return {
        createChecklistItem,
        updateChecklistItem,
        deleteChecklistItem,
        addChecklistItem,
        removeChecklistItem,
    };
});