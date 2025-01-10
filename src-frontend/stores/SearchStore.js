import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchProjectSearch } from '@/stores/fetch/SearchFetcher';

export const useSearchStore = defineStore('search', () => {
    const isSearching = ref(false);
    const isLoading = ref(false);
    const searchResults = ref(null);

    const toggleIsSearching = () => {
        isSearching.value = !isSearching.value;
    };

    const search = (project, search) => {
        return new Promise((resolve) => {
            fetchProjectSearch(project.id, search).then((searchResultsResponse) => {
                searchResults.value = searchResultsResponse;
                isLoading.value = false;

                resolve(searchResults);
            });
        });
    };

    return {
        isSearching,
        isLoading,
        searchResults,

        toggleIsSearching,
        search,
    }
});