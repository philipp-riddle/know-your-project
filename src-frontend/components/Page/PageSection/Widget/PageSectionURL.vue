<template>
    <div
        class="card m-0 p-0 section-card section-card-url section-card-small w-100"
    >
        <div class="card-body m-0 p-0 d-flex flex-column">
            <div
                class="p-4 pt-3 pb-3 url-input-column d-flex flex-column gap-1"
            >
                <div class="d-flex flex-row align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-row align-items-center gap-3">
                        <a
                            v-if="pageSection.pageSectionURL.isInitialized"
                            :href="pageSection.pageSectionURL.url" target="_blank"
                        >
                            <img
                                v-if="pageSection.pageSectionURL.faviconUrl !== null"
                                :src="pageSection.pageSectionURL.faviconUrl"
                                :alt="'Favicon image for ' + pageSection.pageSectionURL.url"
                                class="favicon-image"
                            >
                        </a>
                        <div>
                            <h5 
                                v-if="pageSection.pageSectionURL.isInitialized"
                                class="m-0"
                            >
                                <TextEditor
                                    class="flex-fill"
                                    :text=" pageSection.pageSectionURL.name"
                                    @onChange="onChangeName"
                                    @enter="onChangeName"
                                    placeholder="URL name"
                                />
                            </h5>

                            <a
                                v-if="pageSection.pageSectionURL.isInitialized"
                                :href="pageSection.pageSectionURL.url"
                                target="_blank"
                            >
                                {{ displayedUrl }}
                            </a>
                            <TextEditor
                                v-else
                                class="flex-fill"
                                :text="currentUrl"
                                @onChange="currentUrl = $event"
                                @enter="submit"
                                :focus="true"
                                placeholder="Enter an URL and press Enter"
                                :disabled="isLoading"
                            />

                            <p v-if="!isLoading && !canSubmit" class="text-danger">Invalid URL</p>
                        </div>
                    </div>

                    <button
                        v-if="!pageSection.pageSectionURL.isInitialized"
                        class="btn btn-dark"
                        @click="submit"
                        v-tooltip="'Initialize URL and fetch metadata'"
                        :disabled="!canSubmit"
                    >
                        <div v-if="isLoading" class="spinner-border spinner-border-sm white" role="status">
                            <span class="visually-hidden">Loading search...</span>
                        </div>
                        <font-awesome-icon v-else :icon="['fas', 'check']" />
                    </button>
                </div>
            </div>

            <div
                v-if="pageSection.pageSectionURL.isInitialized"
                :style="backgroundCoverImageStylings"
                class="url-description"
            >
                <div class="p-3">
                    <p class="m-0 text-muted">
                        <TextEditor
                            class="flex-fill"
                            :text=" pageSection.pageSectionURL.description ?? ''"
                            @onChange="onChangeDescription"
                            @enter="onChangeDescription"
                            placeholder="URL description"
                        />
                    </p>
                </div>
            </div>
        </div>
    </div> 
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import TextEditor from '@/components/Util/TextEditor.vue';

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
    const pageSection = ref(props.pageSection);
    const currentUrl = ref(props.pageSection.pageSectionURL.url);
    const isLoading = ref(false);

    const backgroundCoverImageStylings = computed(() => {
        if (pageSection.value.pageSectionURL.coverImageUrl === null) {
            return {};
        }

        return {
            // add the background image and overlay it with a white gradient (almost white to make a good contrast with black)
            background: 'linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url(' + pageSection.value.pageSectionURL.coverImageUrl + ')',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    });

    const displayedUrl = computed(() => {
        const maxLength = 50;

        if (pageSection.value.pageSectionURL.url.length > maxLength) {
            return pageSection.value.pageSectionURL.url.substr(0, maxLength) + '...';
        }

        return pageSection.value.pageSectionURL.url;
    });

    const onChangeName = (newName) => {
        pageSection.value.pageSectionURL.name = newName;
        isDebouncing.value = true;

        debouncedSubmit();
    };

    const onChangeDescription = (newDescription) => {
        pageSection.value.pageSectionURL.description = newDescription;
        isDebouncing.value = true;

        debouncedSubmit();
    };

    const canSubmit = computed(() => {
        if (isLoading.value) {
            return false;
        }

        try {
            new URL(currentUrl.value);
            return true;
        } catch (e) {
            return false;
        }
    });

    const isDebouncing = ref(false);
    const debouncedSubmit = useDebounceFn(() => submit(null, true), 500);

    const submit = (text, isDebounce=false) => {
        if (!canSubmit.value) {
            return;
        }

        // this protects us against double submissions when the user presses enter + the debouncing time is not over
        if (isDebounce && !isDebouncing.value) {
            return;
        }

        isDebouncing.value = false;

        let updatedPageSectionURL = {
            pageSectionURL: {
                url: currentUrl.value,
            },
        };

        if (pageSection.value.pageSectionURL.isInitialized) {
            updatedPageSectionURL.pageSectionURL.name = pageSection.value.pageSectionURL.name;
            updatedPageSectionURL.pageSectionURL.description = pageSection.value.pageSectionURL.description;
        }

        isLoading.value = true;
        props.onPageSectionSubmit(updatedPageSectionURL).then((updatedSection) => {
            pageSection.value = updatedSection;
        }).finally(() => {
            isLoading.value = false;
        });
    };
</script>