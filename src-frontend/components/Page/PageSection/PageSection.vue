<template>
    <div class="page-section row" :page-section="pageSection.id">
        <div class="col-sm-1">
            <div class="section-options d-flex flex-row gap-3">
                <PageSectionInfo :pageSection="pageSection" />
                <button class="btn">
                    <span class="black"><font-awesome-icon :icon="['fas', 'grip-vertical']" /></span>
                </button>
            </div>
        </div>
        <PageSectionText
            v-if="pageSection.pageSectionText != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmit(pageSection, sectionItem)"
        />
        <PageSectionChecklist
            v-if="pageSection.pageSectionChecklist != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmit(pageSection, sectionItem)"
        />
    </div>
</template>

<script setup>
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionInfo from '@/components/Page/PageSection/PageSectionInfo.vue';
    import PageSectionChecklist from '@/components/Page/PageSectionChecklist.vue';
    import PageSectionText from '@/components/Page/PageSectionText.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

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
        index: {
            type: Number,
            required: false,
            default: null,
        },
    });
    const pageSectionStore = usePageSectionStore();
</script>

<style scoped>
    .section-options:not(.active) {
        opacity: 0.0 !important;
        display: none;
    }

    .page-section:hover > div > .section-options {
        /* display: block; */
        /* display: 1.0 !important; */
        opacity: 1.0 !important;
        transition: 'opacity' 0.5s 'ease-in-out';
    }

    /* .section-options {
        display: flex;
        align-items: center;
    } */
</style>