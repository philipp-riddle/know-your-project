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
                <div class="modal-body mt-xl-3 mt-sm-2 p-3 gap-3 d-flex flex-column gap-2">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <button
                                class="btn btn-sm btn-dark d-flex flex-row align-items-center gap-2"
                                v-tooltip="tooltip"
                                @click="toggleForcedIsAskingQuestion"
                            >
                                <font-awesome-icon :icon="['fas', icon]" />
                                <span>{{ label }}</span>
                            </button>
                        </div>
                        <div class="d-flex flex-row align-items-center justify-content-between gap-2 p-3 ps-4 pe-4 search-box">
                            <TextEditor
                                class="flex-fill"
                                :text="currentSearch"
                                @onChange="onChangeSearch"
                                @enter="search"
                                :focus="true"
                                :placeholder="placeholder"
                                :disabled="searchStore.isLoading"
                            />
                            <div class="d-flex flex-row align-items-center gap-2">
                                <button class="btn btn-dark" @click.stop="search" :disabled="canSearch">
                                    <div v-if="searchStore.isLoading" class="spinner-border spinner-border-sm white" role="status">
                                        <span class="visually-hidden">Loading search...</span>
                                    </div>
                                    <font-awesome-icon v-else  :icon="['fas', 'search']" />
                                </button>
                                <button class="btn btn-dark-gray" @click.stop="onResetSearch">
                                    <font-awesome-icon :icon="['fas', 'times']" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-4">
                        <div v-if="searchStore.answer != null" class="card">
                            <div class="card-body">
                                <div>
                                    <span v-html="searchStore.answer.content"></span>
                                </div>
                                <div class="d-flex flex-row gap-3 justify-content-end">
                                    <button
                                        class="btn btn-dark-gray"
                                        v-tooltip="'Copy answer to clipboard'"
                                        @click.stop="() => copy(searchAnswer)"
                                    >
                                        <font-awesome-icon :icon="['fas', 'copy']" />
                                    </button>
                                    <button
                                        class="btn btn-dark"
                                        v-tooltip="'Save answer to page'"
                                        @click.stop="saveAnswerToPage"
                                    >
                                        <font-awesome-icon :icon="['fas', 'floppy-disk']" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="searchStore.searchResults != null"
                            class="d-flex flex-column gap-2 justify-content-start"
                        >
                            <div v-if="searchStore.answer != null" class="d-flex flex-row justify-content-start">
                                <span class="mt-3 btn btn-sm btn-dark d-flex flex-row align-items-center gap-2">
                                    <font-awesome-icon :icon="['fas', 'diagram-project']" />
                                <span>Relevant context</span>
                                </span>
                            </div>
                            <div class="card m-0 p-0">
                                 <div class="card-body m-0 p-0 d-flex flex-column gap-3 mt-3">
                                    <SearchResult
                                        v-if="searchStore.searchResults.length > 0"
                                        v-for="result in searchStore.searchResults"
                                        :key="result.id"
                                        :result="result"
                                        @searchResultClick="hideModal"
                                    />
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
            </div>
        </div>
    </div>
</template>

<script setup>
    import { Modal } from "bootstrap";
    import { computed, ref, watch, nextTick } from "vue";
    import { useRouter } from 'vue-router';
    import { useDebounceFn, useClipboard, onKeyStroke } from '@vueuse/core';
    import SearchResult from '@/components/Search/SearchResult.vue';
    import TextEditor from '@/components/Util/TextEditor.vue';
    import { fetchProjectPageSave } from '@/stores/fetch/GenerationFetcher.js';
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const router = useRouter();
    const searchStore = useSearchStore();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const searchModal = ref(null);
    const currentSearch = ref('');
    const oldSearchTerm = ref('');
    const isDebouncing = ref(false); // we need this helper variable to determine if we are debouncing or not AND to cancel the debounce if enter was clicked in the meantime
    const isForcedAskingQuestion = ref(null); // user can switch between the modes; if null we use the default detection; if true we force asking a question; if false we force searching
    const isAskingQuestion = ref(false);

    const searchAnswer = computed (() => {
        if (!searchStore.answer) {
            return '';
        }

        return `<h3>${searchStore.answer.title}</h3><p>${searchStore.answer.content}</p>`;
    });
    const { text, copy, copied, isSupported } = useClipboard({ searchAnswer })

    const tooltip = computed(() => {
        const isAsk = isForcedAskingQuestion.value != null ? isForcedAskingQuestion.value : isAskingQuestion.value;

        return isAsk ? 'Clickt to search by a term instead' : 'Click to ask a question instead';
    });
    const label = computed(() => {
        const isAsk = isForcedAskingQuestion.value != null ? isForcedAskingQuestion.value : isAskingQuestion.value;

        return isAsk ? 'Asking a question' : 'Searching';
    });
    const icon = computed(() => {
        const isAsk = isForcedAskingQuestion.value != null ? isForcedAskingQuestion.value : isAskingQuestion.value;

        return isAsk ? 'microchip' : 'search';
    });

    const canSearch = computed(() => {
        return currentSearch.value === '' || searchStore.isLoading;
    });

    const placeholder = computed(() => {
        if(!isForcedAskingQuestion.value) {
            return 'Search or ask anything';
        }

        return isForcedAskingQuestion.value ? 'Ask a question' : 'Search';
    })

    // watch for changes in the search store and if the user is searching;
    // if yes show the modal
    watch(() => searchStore.isSearching, (newValue) => {
        if (newValue) {
            showModal();
        }
    });

    // important: the search modal is required by default, i.e. the user cannot escape / click out of it.
    // Here we listen for the escape key and close the modal if it is pressed. This gives us more control about the modal and its data.
    onKeyStroke('Escape', (e) => {
        e.preventDefault();
        hideModal();
    });

    const showModal = async () => {
        const modal = new Modal(document.getElementById('searchModal'));
        modal.show();
    };

    const hideModal = () => {
        const searchModal = document.getElementById('searchModal');

        // remove show class from modal and remove modal backdrop
        searchModal.classList.remove('show');
        searchModal.style.display = 'none';
        document.getElementsByClassName('modal-backdrop')[0]?.remove();

        // only set the search store to not searching;
        // we do not reset the search store as the user might want to go back to the search results.
        searchStore.isSearching = false;
    };

    const checkIfUserClickedOutside = (e) => {
        // when the user clicks on the modal itself, we hide the modal as this is outside of the search dialogue
        if (e.target.classList.contains('modal')) {
            hideModal();
        }
    };

    // the user can override our default detection if it is a question or search query - 
    // this needs a separate function as this needs quite some additional logic.
    const toggleForcedIsAskingQuestion = () => {
        if (isForcedAskingQuestion.value === null) {
            isForcedAskingQuestion.value = !isAskingQuestion.value;
        } else {
            isForcedAskingQuestion.value = !isForcedAskingQuestion.value;
        }
        
        // trigger a new search
        search();
    }

    const onChangeSearch = (newSearch) => {
        currentSearch.value = newSearch;
        isDebouncing.value = true;

        // split the search term into words; if it's >= 4 or if it includes a question mark we assume it's a question
        isAskingQuestion.value = currentSearch.value.split(' ').length >= 4 || currentSearch.value.includes('?');

        debouncedSearch();
    }

    const onResetSearch = () => {
        currentSearch.value = '';
        oldSearchTerm.value = '';
        searchStore.resetStore();
    }

    const search = (isDebounce=false) => {
        if (searchStore.isLoading || (isDebounce && !isDebouncing.value)) {
            return;
        }

        // kill the debounce by setting the isDebouncing variable to false;
        // this can triggered by pressing the enter key; the debounce request is thus no longer needed
        isDebouncing.value = false;

        if (currentSearch.value === oldSearchTerm.value) {
            return;
        }

        searchStore.isLoading = true;
        var isSearch = true;

        if (isForcedAskingQuestion.value == null) {
            isSearch = !isAskingQuestion.value;
        } else {
            isSearch = !isForcedAskingQuestion.value;
        }

        if (isSearch) {
            searchStore.search(projectStore.selectedProject, currentSearch.value);
        } else {
            searchStore.ask(projectStore.selectedProject, currentSearch.value);
        }
    }
    const debouncedSearch = useDebounceFn(() => search(true), 2000); // long debounce time as we want to wait for the user to finish typing; if the user wants to search immediately, they can press enter

    const saveAnswerToPage = () => {
        if (searchStore.answer == null) {
            return;
        }

        fetchProjectPageSave(
            projectStore.selectedProject.id,
            null, // pass null as page ID; we want to create a new page
            searchStore.answer.title,
            searchStore.answer.content,
        ).then((savedPage) => {
            searchStore.answer = null;
            searchStore.searchResults = null;

            pageStore.setSelectedPage(savedPage, true).then((selectedPage) => {
                router.push({ name: 'WikiPage', params: { id: selectedPage.id } });
            });
        }).finally(() => {
            hideModal();
        });
    }
</script>

<style scoped lang="scss">
    .modal-dialog {
        padding-left: 15%;
        padding-right: 15%;
        padding-top: 2%;
        padding-bottom: 0%;
        
        // on mobile we want a margin on the top
        @media (max-width: 768px) {
            padding-left: 0%;
            padding-right: 0%;
            padding-top: 10%;
            padding-bottom: 0%;
        }

        // on REALLY large devices we want a larger padding
        @media (min-width: 1600px) {
            padding-left: 30%;
            padding-right: 30%;
            padding-top: 5%;
            padding-bottom: 0%;
        }
    }
</style>