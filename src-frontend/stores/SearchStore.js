import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchProjectAsk } from '@/stores/fetch/GenerationFetcher';
import { fetchProjectSearch } from '@/stores/fetch/SearchFetcher';

export const useSearchStore = defineStore('search', () => {
    const isSearching = ref(false);
    const isLoading = ref(false);
    const searchResults = ref(null);
    const searchPromise = ref(null);
    const answer = ref(null); // if a question was asked we also store the answer here

    const toggleIsSearching = () => {
        isSearching.value = !isSearching.value;
    };

    const search = (project, search) => {
        const currentSearchPromise = new Promise((resolve) => {
            fetchProjectSearch(project.id, search).then((searchResultsResponse) => {
                if (searchPromise.value === null || searchPromise.value === currentSearchPromise) {
                    searchResults.value = searchResultsResponse;
                    isLoading.value = false;
                    searchPromise.value = null;
                    answer.value = null;

                    resolve(searchResults);
                } else {
                    searchPromise.value.then((searchResults) => {
                        resolve(searchResults);
                    });
                }
            });
        });
        searchPromise.value = currentSearchPromise;
    };

    const ask = (project, search) => {
        const currentAskPromise = new Promise((resolve) => {
            fetchProjectAsk(project.id, search).then((response) => {
                if (searchPromise.value === null || searchPromise.value === currentAskPromise) {
                    searchResults.value = response.searchResults;
                    isLoading.value = false;
                    searchPromise.value = null;
                    answer.value = response.answer;

                    resolve(searchResults.value);
                } else {
                    searchPromise.value.then((searchResults) => {
                        resolve(searchResults);
                    });
                }
            });
        });
        searchPromise.value = currentAskPromise;
    }

    const resetStore = () => {
        searchResults.value = null;
        answer.value = null;
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