<template>
    <div
        class="modal fade search-modal"
        id="searchModal"
        tabindex="-1"
        aria-hidden="true"
        ref="searchModal"
        @blur="hideSearch"
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
                        @keyup.esc="hideSearch"
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
                            <SearchResult :result="result" />
                        </div>
                        <div v-else class="alert alert-info mt-3" role="alert">
                            <p>No results found.</p>
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

    const searchStore = useSearchStore();
    const projectStore = useProjectStore();
    const route = useRoute();
    const router = useRouter();
    const searchModal = ref(null);
    const searchInput = ref(null);

    watch(() => searchStore.isSearching, (newValue) => {
        console.log('watch', searchStore.isSearching);
        if (newValue) {
            showModal();
        } else {
            hideModal();
        }
    });

    const showModal = async () => {
        const modal = new Modal(document.getElementById('searchModal'));
        modal.show();

        await nextTick();
        searchInput.value.focus(); // @todo does not work somehow
    };

    const hideModal = () => {
        console.log('hide modal');
        const modal = new Modal(document.getElementById('searchModal'));
        modal.hide();
    };

    const hideSearch = () => {
        hideModal();
        searchStore.isSearching = false;
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
        padding-top: 5%;
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