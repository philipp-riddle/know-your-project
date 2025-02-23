<template>
    <div v-if="pageSection" class="page-section page-section-container row" :page-section="pageSection.id">
        <div class="col-sm-12 col-md-9 col-xl-2 m-0 p-2">
            <div
                v-if="pageSection.id != null"
                class="d-flex flex-row gap-3 justify-content-between"
                :class="{
                    // if the deletion dropdown is open the control should be completely visible and not only on hover
                    'section-options': !isDeletionDropdownVisible,
                }"
            >
                <div class="d-flex flex-row gap-3 align-items-center">
                    <DeletionButton
                        label="page section"
                        @onShowDropdown="isDeletionDropdownVisible = true"
                        @onHideDropdown="isDeletionDropdownVisible = false"
                        @onConfirm="onPageSectionDeleteClick"
                    />
                    <PageSectionInfo :pageSection="pageSection" />              
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
            <PageSectionCalendarEvent
                v-once
                v-else-if="pageSection.calendarEvent != null"
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
    import PageSectionAIPrompt from '@/components/Page/PageSection/Widget/PageSectionAIPrompt.vue';
    import PageSectionCalendarEvent from '@/components/Page/PageSection/Widget/PageSectionCalendarEvent.vue';
    import PageSectionChecklist from '@/components/Page/PageSection/Widget/PageSectionChecklist.vue';
    import PageSectionEmbeddedPage from '@/components/Page/PageSection/Widget/PageSectionEmbeddedPage.vue';
    import PageSectionUpload from '@/components/Page/PageSection/Widget/PageSectionUpload.vue';
    import PageSectionSummary from '@/components/Page/PageSection/Widget/PageSectionSummary.vue';
    import PageSectionText from '@/components/Page/PageSection/Widget/PageSectionText.vue';
    import PageSectionURL from '@/components/Page/PageSection/Widget/PageSectionURL.vue';

    import PageSectionInfo from '@/components/Page/PageSection/PageSectionInfo.vue';
    import PageSectionThreadButton from '@/components/Page/PageSection/PageSectionThreadButton.vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { usePageSectionAccessibilityHelper } from '@/composables/PageSectionAccessibilityHelper.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { computed, nextTick, ref, watch } from 'vue';
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
    const currentSubmitPromise = ref(null); // we can use this to choose whether to force the re-render of the page section or not.

    // depending on this we want to show all icons from the page section or not.
    // better UX as the icons do not disappear when the dropdown is still there.
    const isDeletionDropdownVisible = ref(false);

    // debounce the page section submit.
    // this means that the function will only be called after the last call to it has been made after 300ms.
    // this saves requests and makes the UI more responsive.
    const debouncedPageSectionSubmit = useDebounceFn((section, sectionItem) => props.onPageSectionSubmit(section, sectionItem), 300);

    watch (() => props.pageSection, async (newValue) => {
        // problem: vue does not recognize it needs to re-render the component as the ID does not change.
        // solution: force re-render by changing the page section to null for one tick and then back to the new value.

        // we have to make an exception though:
        // if the user updated the page section we do not want a sync; this would overwrite the data the user is currenly typing / editing.
        if (currentSubmitPromise.value) {
            return;
        }

        pageSection.value = null;
        await nextTick();
        pageSection.value = newValue;
    });

    const onPageSectionSubmitHandler = async (section, sectionItem) => {
        return new Promise(async (resolve) => {
            const currentPromise = currentSubmitPromise.value = debouncedPageSectionSubmit(section, sectionItem).then((updatedSection) => {
                if (updatedSection) {
                    pageSection.value = updatedSection;

                    if (currentSubmitPromise.value == currentPromise) {
                        currentSubmitPromise.value = null; // only reset the promise here if it's still the same; could have changed while loading the requests.
                    }

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