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
                            <h5 v-if="pageSection.pageSectionURL.isInitialized" class="m-0">{{ pageSection.pageSectionURL.name }}</h5>
                            
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
                v-if="pageSection.pageSectionURL.description !== null"
                :style="backgroundCoverImageStylings"
                class="url-description"
            >
                <div class="p-3">
                    <p class="m-0 text-muted">{{ pageSection.pageSectionURL.description }}</p>
                </div>
            </div>
        </div>
    </div> 
</template>

<script setup>
    import { computed, ref } from 'vue';
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
    })

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

    const submit = () => {
        if (!canSubmit.value) {
            return;
        }

        isLoading.value = true;
        props.onPageSectionSubmit({
            pageSectionURL: {
                url: currentUrl.value,
            },
        }).then((updatedSection) => {
            pageSection.value = updatedSection;
        }).finally(() => {
            isLoading.value = false;
        });
    };
</script>