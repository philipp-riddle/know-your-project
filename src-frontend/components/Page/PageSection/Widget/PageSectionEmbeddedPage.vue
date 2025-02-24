<template>
    <div class="card section-card section-card-small w-100">
        <div class="card-body p-2">
            <div v-if="embeddedPage.page?.id">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h4 class="m-0">
                        <router-link
                            :to="{ name: 'WikiPage', params: { id: embeddedPage.page.id } }"
                            v-tooltip="'Open page '"
                            @click="pageStore.setSelectedPage(embeddedPage.page)"
                        >
                            {{ embeddedPage.page.name }}
                        </router-link>
                        &nbsp;&nbsp;
                        <router-link
                            :to="{ name: 'WikiPage', params: { id: embeddedPage.page.id } }"
                            class="btn btn-sm btn-dark"
                            target="_blank"
                            v-tooltip="'Open page in new tab'"
                        >
                            <font-awesome-icon :icon="['fas', 'arrow-up-right-from-square']" />
                        </router-link>
                    </h4>
                    <button
                        class="btn btn-sm black"
                        @click="onSelectPage(null)"
                        v-tooltip="'Select other page'"
                    >
                        <font-awesome-icon :icon="['fas', 'xmark']" />
                    </button>
                </div>
                <p>{{ embeddedPageExtract }}</p>
            </div>
            <div v-else class="d-flex flex-column gap-2">
                <div class="d-flex flex-row justify-content-between">
                    <h5 class="bold m-0">Connect page</h5>
                    <button class="btn btn-sm m-0 p-0" @click="debouncedFetchApiForResults"><font-awesome-icon class="black" :icon="['fas', 'arrow-rotate-right']" /></button>
                </div>
                <h5><input
                    type="text"
                    class="form-control p"
                    placeholder="Search..."
                    ref="searchInput"
                    @keyup="debouncedFetchApiForResults"
                ></h5>

                <div v-if="searchResults">
                    <div
                        v-for="result in searchResults"
                        key="result.id"
                    >
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <p class="m-0 p-0 col-sm-11">{{ result.name }}</p>
                            <button
                                class="btn btn-sm black col-sm-1"
                                @click="onSelectPage(result)"
                            >
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </button>
                        </div>
                        <hr class="m-0 p-0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { fetchGetPageList } from '@/stores/fetch/PageFetcher.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { computed, ref, onMounted } from 'vue';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        pageSection: { // this prop is only set if we have an already existing section
            type: Object,
            required: false,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
    });
    const searchInput = ref(null);
    const searchResults = ref([]);
    const userStore = useUserStore();
    const pageStore = usePageStore();
    const pageSectionStore = usePageSectionStore();
    const embeddedPage = ref(props.pageSection?.embeddedPage);
    const embeddedPageExtract = computed(() => {
        if (!embeddedPage.value.page) {
            return null;
        }

        if (typeof embeddedPage.value.page != 'object') {
            console.error('embeddedPage is not an object, found ' + typeof embeddedPage.value.page);
            console.error(embeddedPage.value.page);

            return '';
        }

        if (embeddedPage.value.page.pageTabs.length === 0) {
            return ''; // if the page has no tabs we don't need to show any text / extract; could also be due to the page not being loaded yet
        }

        for (const pageSection of embeddedPage.value.page.pageTabs[0]?.pageSections ?? []) {
            if (pageSection.pageSectionText) {
                let text = pageSection.pageSectionText.content;
                let originalTextLength = text.length;

                // remove any HTML and only return the first 30 characters
                text = text.replace(/<[^>]*>?/gm, '');
                text = text.substring(0, 50);

                if (text.length < originalTextLength) {
                    text += '...';
                }

                return text;
            }
        }

        return null;
    });

    onMounted(() => {
        // focus the search input when the component is mounted, if available
        searchInput.value?.focus();

        // we only need to load results if the user has not selected a page yet
        if (!embeddedPage.value.page) {
            userStore.getCurrentUser().then(() => {
                debouncedFetchApiForResults(); // if the user is loaded we can fetch the results when initializing this component
            });
        }

        pageSectionStore.selectedPageSection = props.pageSection;
    });

    const fetchApiForResults = () => {
        // load with the current project id, do NOT include user notes, pass on the value of the search input to filter the results, limit the results to 5, and exclude the current page in the results
        fetchGetPageList(userStore.currentUser.selectedProject.id, searchInput?.value?.value ?? '', 5, pageStore.selectedPage?.id ?? '').then((foundPages) => {
            searchResults.value = foundPages;
        });
    }
    const debouncedFetchApiForResults = useDebounceFn(fetchApiForResults, 200); // use a debounce to prevent too many requests

    const onSelectPage = (page) => {
        if (page) {
            // only submit if the page is not null; this makes it possible to deselect the page in the frontend while not deleting in the backend.
            // this makes it possible for the user to easily change the embedded page
            props.onPageSectionSubmit({
                embeddedPage: {
                    page: page.id,
                },
            }).then((updatedSection) => {
                embeddedPage.value = updatedSection.embeddedPage; // update the embedded page in the frontend to get the serialised page object with the tabs to generate the extract for the new embedded page
            });
        } else {
            fetchApiForResults(); // if the user deselects the page we need to load the search results again
        }

        embeddedPage.value.page = page;
    }
</script>
