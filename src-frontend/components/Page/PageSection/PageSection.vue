<template>
    <div class="page-section-container row" :page-section="pageSection.id">
        <div class="col-sm-12 col-md-9 col-xl-2">
            <div class="section-options d-flex flex-row gap-3 justify-content-between" v-if="pageSection.id != null">
                <div class="d-flex flex-row gap-3 align-items-center">
                    <PageSectionInfo :pageSection="pageSection" />
                    <button class="btn btn-light-gray p-0 m-0" v-tooltip="'Delete'" @click="onPageSectionDeleteClick">
                        <font-awesome-icon class="" :icon="['fas', 'trash']" />
                    </button>                    
                </div>
                <div class="d-flex flex-row gap-4 align-items-center">
                    <button class="btn p-0 m-0" v-tooltip="'Drag to rearrange order'">
                        <span class="black"><font-awesome-icon :icon="['fas', 'grip-vertical']" /></span>
                    </button>
                    <span class="btn btn-lg m-0 p-0" v-if="pageSectionIcon" v-tooltip="pageSectionTooltip">
                        <font-awesome-icon :icon="['fas', pageSectionIcon]" />
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-9 col-xl-10 d-flex flex-row gap-5">
            <!-- the PageSection elements all need the v-once directive! -->
            <!-- this is important to not cause any re-renders of the object when creating or updating the pageSection ref value. -->
            <!-- otherwise this could interrupt the user flow, e.g. by losing the input focus while typing. -->
            <!-- @todo we need to rethink this when we introduce real time editing as this will require background updates -->
            <PageSectionText
                v-once
                v-if="pageSection.pageSectionText != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionChecklist
                v-once
                v-else-if="pageSection.pageSectionChecklist != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionURL
                v-once
                v-else-if="pageSection.pageSectionURL != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionUpload
                v-once
                v-else-if="pageSection.pageSectionUpload != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionEmbeddedPage
                v-once
                v-else-if="pageSection.embeddedPage != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionAIPrompt
                v-once
                v-else-if="pageSection.aiPrompt != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <PageSectionSummary
                v-once
                v-else-if="pageSection.pageSectionSummary != null"
                :pageSection="pageSection"
                :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
            />
            <div v-else class="alert alert-danger">
                <p>Unknown section type - cannot render.</p>
            </div>

            <PageSectionThreadButton :pageSection="pageSection" />
        </div>
    </div>
</template>

<script setup>
    // we need to import all the PageSection components here to make sure they are available in the template; huge if stament bundles them all together
    import PageSectionChecklist from '@/components/Page/PageSection/Widget/PageSectionChecklist.vue';
    import PageSectionEmbeddedPage from '@/components/Page/PageSection/Widget/PageSectionEmbeddedPage.vue';
    import PageSectionUpload from '@/components/Page/PageSection/Widget/PageSectionUpload.vue';
    import PageSectionSummary from '@/components/Page/PageSection/Widget/PageSectionSummary.vue';
    import PageSectionText from '@/components/Page/PageSection/Widget/PageSectionText.vue';
    import PageSectionURL from '@/components/Page/PageSection/Widget/PageSectionURL.vue';
    import PageSectionAIPrompt from '@/components/Page/PageSection/Widget/PageSectionAIPrompt.vue';

    import PageSectionInfo from '@/components/Page/PageSection/PageSectionInfo.vue';
    import PageSectionThreadButton from '@/components/Page/PageSection/PageSectionThreadButton.vue';
    import { usePageSectionAccessibilityHelper } from '@/composables/PageSectionAccessibilityHelper.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { computed, ref, onMounted } from 'vue';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        pageSection: {
            type: Object,
            required: true,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
        onPageSectionDelete: {
            type: Function,
            required: true,
        },
    });
    const pageSectionStore = usePageSectionStore();
    const pageSection = ref(props.pageSection);
    const debouncedPageSectionSubmit = useDebounceFn((section, sectionItem) => props.onPageSectionSubmit(section, sectionItem), 500);

    onMounted(() => {
        // our non-initialized objects have a string ID to make it easier to identify them for Vue - we filter them out here
        if (isNaN(pageSection.value.id)) {
            delete pageSection.value.id;
        }
    });

    const onPageSectionSubmitHandler = async (section, sectionItem) => {
        return new Promise(async (resolve) => {
            debouncedPageSectionSubmit(section, sectionItem).then((updatedSection) => {
                if (updatedSection) {
                    pageSection.value = updatedSection;
                    resolve(updatedSection);
                }
            });
        });
    };

    const onPageSectionDeleteClick = async () => {
        await pageSectionStore.deleteSection(props.pageSection);
    };

    const accessibilityHelper = usePageSectionAccessibilityHelper();
    const pageSectionIcon = computed(() => {
        return accessibilityHelper.getIcon(pageSection.value);
    });
    const pageSectionTooltip = computed(() => {
        return accessibilityHelper.getTooltip(pageSection.value);
    });
</script>

<style scoped>
    .section-options:not(.active) {
        opacity: 0.0 !important;
        display: none;
    }

    .page-section-container:hover > div > .section-options {
        opacity: 1.0 !important;
        transition: opacity 0.2s ease-in-out;
    }
</style>