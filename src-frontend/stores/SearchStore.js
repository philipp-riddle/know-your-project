import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchProjectAsk } from '@/stores/fetch/GenerationFetcher';
import { fetchProjectSearch } from '@/stores/fetch/SearchFetcher';

export const useSearchStore = defineStore('search', () => {
    const isSearching = ref(false);
    const isLoading = ref(false);
    const searchResults = ref(null);
    const answer = ref(null); // if a question was asked we also store the answer here

    const toggleIsSearching = () => {
        isSearching.value = !isSearching.value;
    };

    const search = (project, search) => {
        return new Promise((resolve) => {
            fetchProjectSearch(project.id, search).then((searchResultsResponse) => {
                searchResults.value = searchResultsResponse;
                answer.value = null;
                isLoading.value = false;

                resolve(searchResults);
            });
        });
    };

    const ask = (project, search) => {
        return new Promise((resolve) => {
            fetchProjectAsk(project.id, search).then((response) => {
                searchResults.value = response.searchResults;
                answer.value = response.answer;
                isLoading.value = false;

                resolve(searchResults);
            });
        });
    }

    const resetStore = () => {
        isSearching.value = false;
        isLoading.value = false;
    }

    return {
        isSearching,
        isLoading,
        searchResults,
        answer,

        toggleIsSearching,
        search,
        ask,
        resetStore,
    }
});