<template>
    <div class="page-section">
        <PageSectionText
            v-if="pageSection.pageSectionText != null"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmit(pageSection, sectionItem)"
        />
        <PageSectionChecklist
            v-if="pageSection.pageSectionChecklist != null"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmit(pageSection, sectionItem)"
        />

        <div class="section-options">
            <div class=" d-flex flex-row gap-3">
                <div class="dropdown">
                    <h5 class="dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                        <font-awesome-icon :icon="['fas', 'ellipsis']" />
                    </h5>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><span class="dropdown-item" href="#" @click.stop="onPageSectionDelete(pageSection)">Delete</span></li>
                    </ul>
                </div>
                <p class="m-0"><strong>{{ pageSection.author.email }}</strong></p>
                <small>{{ pageSection.createdAt }}</small>
            </div>
            <PageSectionCreateWidget :onCreate="(sectionItem) => onPageSectionSubmit(null, sectionItem)" />
        </div>
    </div>
</template>

<script setup>
    import PageSectionCreateWidget from '@/components/Page/PageSectionCreateWidget.vue';
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
    });
    const pageSectionStore = usePageSectionStore();
</script>

<style scoped>
    .section-options {
        opacity: 0.0 !important;
        display: none;
    }

    .page-section:hover > .section-options {
        display: block;
        display: 1.0 !important;
        opacity: 1.0 !important;
        transition: 'opacity' 0.5s 'ease-in-out';
    }

    /* .section-options {
        display: flex;
        align-items: center;
    } */
</style>