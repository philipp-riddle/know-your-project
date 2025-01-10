<template>
    <div
        class="modal fade search-modal"
        id="searchModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        aria-hidden="true"
        ref="searchModal"
        @click="checkIfUserClickedOutside"
    >
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-body mt-xl-3 mt-sm-2 p-3">
                    <input
                        type="text"
                        placeholder="Search for anything: text, URLs, checklist items, ..."
                        class="form-control"
                        ref="searchInput"
                        @keyup.enter="search"
                        @keyup.esc="hideModal"
                        @keyup="search"
                    >

                    <div
                        v-if="searchStore.isLoading"
                        class="d-flex flex-row justify-content-center"
                    >
                        <div class="spinner-border mt-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div
                        v-else-if="searchStore.searchResults != null"
                        class="search-results d-flex flex-column gap-2 mt-3"
                    >
                        <div v-if="searchStore.searchResults.length > 0" v-for="result in searchStore.searchResults" :key="result.id">
                            <SearchResult
                                :result="result"
                                :searchTerm="searchInput.value"
                                @searchResultClick="hideModal"
                            />
                        </div>
                        <div v-else class="card mt-3" role="alert">
                            <div class="card-body p-4">
                                <p class="m-0">No results found.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { Modal } from "bootstrap";
    import { computed, onMounted, ref, watch, nextTick } from "vue";
    import { useRoute, useRouter } from 'vue-router';
    import { useDebounceFn } from '@vueuse/core';
    import SearchResult from '@/components/Search/SearchResult.vue';
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { onKeyStroke } from '@vueuse/core';

    const searchStore = useSearchStore();
    const projectStore = useProjectStore();
    const route = useRoute();
    const router = useRouter();
    const searchModal = ref(null);
    const searchInput = ref(null);

    // watch for changes in the search store and if the user is searching;
    // if yes show the modal
    watch(() => searchStore.isSearching, (newValue) => {
        if (newValue) {
            showModal();
        }
    });

    // first, we make the modal required.
    // then we listen for the escape key and close the modal if it is pressed. This gives us more control about the actions and what happens with the data
    onKeyStroke('Escape', (e) => {
        e.preventDefault();
        hideModal();
    });

    const showModal = async () => {
        const modal = new Modal(document.getElementById('searchModal'));
        modal.show();

        // @todo somehow does not wokr
        nextTick().then(() => {
            searchInput.value.focus();
        });
    };

    const hideModal = () => {
        const searchModal = document.getElementById('searchModal');

        // remove show class from modal and remove modal backdrop
        searchModal.classList.remove('show');
        searchModal.style.display = 'none';
        document.getElementsByClassName('modal-backdrop')[0]?.remove();

        // reset the search store to make it ready for the next search;
        // we keep the search results and the search term though in case the user wants to try another search result.
        searchStore.resetStore();
    };

    const checkIfUserClickedOutside = (e) => {
        // when the user clicks on the modal itself, we hide the modal as this is outside of the search dialogue
        if (e.target.classList.contains('modal')) {
            hideModal();
        }
    };

    const search = () => {
        searchStore.isLoading = true;
        debouncedSearch(projectStore.selectedProject, searchInput.value.value);
    }

    const debouncedSearch = useDebounceFn((project, searchTerm) => {
        searchStore.search(project, searchTerm);
    }, 300);
</script>

<style scoped lang="sass">
    .modal-dialog {
        padding-left: 30%;
        padding-right: 30%;
        padding-top: 2%;
        padding-bottom: 0%;
        
        // on mobile we want a margin on the top
        @media (max-width: 768px) {
            padding-left: 0%;
            padding-right: 0%;
            padding-top: 10%;
            padding-bottom: 0%;
        }
    }
</style>