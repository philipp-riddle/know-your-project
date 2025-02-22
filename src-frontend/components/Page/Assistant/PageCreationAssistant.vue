<template>
    <div v-if="isPageTabEmpty" class="page-creation-assistant d-flex flex-column gap-3">
        <div class="card card-setup pb-4 p-1">
            <div class="card-body d-flex flex-column gap-3">
                <div class="card-head d-flex flex-row gap-3 align-items-center">
                    <font-awesome-icon :icon="['fas', 'microchip']" />
                    <h4 class="m-0 bold">Setup {{ creationType }}</h4>
                </div>
                <div class="d-flex flex-column gap-2">
                    <label for="title">What do you want to generate?</label>
                    <div class="d-flex flex-row gap-2">
                        <textarea
                            name="intro"
                            type="text" 
                            placeholder="e.g. generate FAQ" 
                            class="form-control black"
                            ref="introInput"
                            @keyup.enter="handleLoad"
                            rows="2"
                        ></textarea>
                        <button
                            class="btn btn-dark"
                            :disabled="introInput?.value?.value === ''"
                            @click="handleLoad"
                        >
                            <div v-if="isLoading" class="spinner-border spinner-border-sm text-white" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <font-awesome-icon v-else :icon="['fas', creationResponse == null ? 'plus': 'arrows-rotate']" />
                        </button>
                    </div>

                    <div v-if="!isLoading && creationResponse != null">
                        <div class="d-flex flex-column align-items-start mb-3" v-if="creationResponse.searchResults.length > 0">
                            <span class="mt-3 btn btn-sm btn-dark d-flex flex-row align-items-center gap-2">
                                <font-awesome-icon :icon="['fas', 'diagram-project']" />
                                <span>Context</span>
                            </span>
                            <div class="row">
                                <div class="col-sm-12 col-md-6" v-for="searchResult in creationResponse.searchResults.splice(0, 2)">
                                    <SearchResult :result="searchResult" :searchTerm="''" :condensed="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else-if="!isLoading" class="m-0 card-label">State a precise prompt to start.</p>
                </div>
            </div>
        </div>

        <div v-if="creationResponse != null">
            <div class="card p-1 mb-3">
                <div class="card-body d-flex flex-row gap-2 align-items-center justify-content-between">
                    <div class="d-flex flex-column gap-2">
                        <div v-if="creationResponse.answer.tag != null" class="d-flex flex-row gap-2 align-items-center">
                            <div class="text-muted d-flex flex-row gap-2 align-items-center">
                                <font-awesome-icon :icon="['fas', 'tag']" />
                                <p class="m-0">Tag</p>
                                <span class="btn btn-tag btn-sm me-1" :style="{'background-color': creationResponse.answer.tag.color}" v-tooltip="'Suggested tag'">&nbsp;&nbsp;&nbsp;</span>
                            </div>
                            <p class="m-0">{{ creationResponse.answer.tag.name }}</p>
                        </div>

                        <p class="m-0">Content was generated. If you want to save the changes click the check button on the right.</p>
                    </div>
                    <button
                        @click="handleSave"
                        :disabled="isSaving"
                        class="btn btn-primary"
                        v-tooltip="'Save generated content to page'"
                    >
                        <div v-if="isSaving" class="spinner-border spinner-border-sm text-white" role="status">
                            <span class="visually-hidden">Saving to page...</span>
                        </div>
                        <font-awesome-icon v-else :icon="['fas', 'check']" />
                    </button>
                </div>
            </div>
            <h2><span v-html="creationResponse.answer.title"></span></h2>
            <span class="card-label"><span v-html="creationResponse.answer.content"></span></span>

            <div v-if="creationResponse.answer.checklist != null">
                <div class="card p-1 mb-3">
                    <div class="card-body d-flex flex-row gap-2 align-items-center justify-content-between">
                        <div class="d-flex flex-column gap-2">
                            <h4>Checklist</h4>
                            <div class="d-flex flex-column gap-2">
                                <li v-for="checklistItem in creationResponse.answer.checklist" class="d-flex flex-row gap-2 align-items-center dark-gray">
                                    <font-awesome-icon :icon="['fas', 'check-square']" />
                                    <p class="m-0"><span v-html="checklistItem"></span></p>
                                </li>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed, watch, ref } from 'vue';
    import { useRouter } from 'vue-router';
    import { usePageStore } from '@/stores/PageStore.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { fetchProjectCreate, fetchProjectPageSave } from '@/stores/fetch/GenerationFetcher.js';
    import SearchResult from '@/components/Search/SearchResult.vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        pageTab: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const pageSectionStore = usePageSectionStore();
    const router = useRouter();
    const introInput = ref(null);
    const isLoading = ref(false);
    const isSaving = ref(false);
    const creationResponse = ref(null);

    // if the page sections change, update the page tab and see if it is still empty
    watch (() => pageSectionStore.displayedPageSections,(newPageSections) => {
        props.pageTab.pageSections = newPageSections;
    });

    const isPageTabEmpty = computed(() => {
        if (props.pageTab.pageSections.length === 0) {
            return true;
        }

        const textContent = props.pageTab.pageSections[0]?.pageSectionText?.content;

        if (props.pageTab.pageSections.length === 1 && (textContent === '' || textContent === '<p></p>')) {
            return true;
        }

        return false;
    });
    const creationType = computed(() => {
        return props.page.task !== null ? 'task' : 'page';
    });

    const handleLoad = () => {
        if (isLoading.value || isSaving.value) {
            return;
        }

        var introPrompt = introInput.value.value.trim();

        if (introPrompt === '') {
            return;
        }

        isLoading.value = true;

        fetchProjectCreate(props.page.id, introPrompt).then((response) => {
            creationResponse.value = response;
        }).finally(() => {
            isLoading.value = false;
        });
    }

    const handleSave = () => {
        if (isSaving.value) {
            return;
        }

        isSaving.value = true;

        fetchProjectPageSave(
            props.page.id,
            creationResponse.value.answer.title,
            creationResponse.value.answer.content,
            creationResponse.value.answer.tag?.id,
            creationResponse.value.answer.checklist ?? [],
        ).then((savedPage) => {
            creationResponse.value = null;
            pageStore.setSelectedPage(savedPage, true).then((selectedPage) => {

                if (selectedPage.task === null) {
                    router.push({ name: 'WikiPage', params: { id: selectedPage.id } });
                } else {
                    router.push({ name: 'TasksDetail', params: { id: selectedPage.task.id } });
                }
            });
        }).finally(() => {
            isSaving.value = false;
        });
    }
</script>