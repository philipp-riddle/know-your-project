import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useTaskStore } from './TaskStore';

export const useFilterStore = defineStore('filter', () => {
    const taskStore = useTaskStore();
    const filterTags = ref([]);

    function addFilterTag(tag) {
        if (filterTags.value && filterTags.value.includes(tag)) {
            return;
        }

        filterTags.value.push(tag);
        reloadContentsWithFilters();
    }

    function removeFilterTag(tag) {
        if (!filterTags.value || !filterTags.value.includes(tag)) {
            return;
        }

        filterTags.value = filterTags.value.filter((t) => t !== tag);
        reloadContentsWithFilters();
    }

    function getFilterTagIds() {
        return filterTags.value.map((filterTag) => filterTag.id);
    }

    function reloadContentsWithFilters() {
        taskStore.getTasks(getFilterTagIds());
    }

    return {
        filterTags,
        addFilterTag,
        removeFilterTag,
    }
});